<?php

namespace Qubeek\NovaStorageInfoCard\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class CardController extends Controller
{
    /**
     * @return Response
     */
    public function refresh()
    {
        /** Validate request */
        $data = request()->validate([
            'disks' => 'required|array|min:0',
        ]);

        /** @var Collection $disks */
        $disks = collect($data['disks']);

        /**
         * Forget all stored values.
         */
        $disks->each(function ($disk) {
            Cache::forget($this->getKey($disk['name']));
        });

        return response()->noContent();
    }

    /**
     * @param int $size
     * @param int $items
     *
     * @return JsonResponse
     */
    public function storage($size = 0, $items = 0)
    {
        $data = request()->validate([
            'disk' => 'required',
        ]);

        /** @var S3Client $client */
        list($s3, $bucket) = $this->getClient($data['disk']);

        /**
         * Check, that we working with S3 client only.
         * Otherwise we need to use different driver.
         * In future we should create extra functions to work with local drivers.
         */
        if ($s3 instanceof S3Client) {

            /**
             * Prepare string key for storing cache or request.
             */
            $key = $this->getKey($data['disk']);

            $ttl = request('cache', 5 * 60);

            return Cache::remember($key, $ttl, function () use ($items, $bucket, $size, $s3) {

                /**
                 * Fetch list of object from storage.
                 */
                $results = $s3->getPaginator('ListObjectsV2', [
                    'Bucket' => $bucket,
                ]);

                /**
                 * Serve over results from request
                 */
                foreach ($results as $result) {
                    $size += array_sum(array_column($result['Contents'], 'Size'));
                    $items += count(array_values($result['Contents']));
                }

                /**
                 * Return success status and data for current disk.
                 * Used choice to create interesting and common view for items.
                 */
                return response()->json([
                    'status' => 200,
                    'size' => $this->bytesToHuman($size),
                    'bucket' => $bucket,
                    'items' => $this->prettyItems($items),
                ], 200);
            });
        } else {
            /** Return error, when the disk doesn't provide S3 compatibility */
            return response()->json([
                'status' => 400,
                'message' => 'The disk doesn\'t provide S3 compatibility',
            ], 400);
        }
    }

    /**
     * Prettify output for bytes.
     *
     * @param $bytes
     *
     * @return string
     */
    protected function bytesToHuman($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Just prettify output.
     *
     * @param int $items
     *
     * @return string
     */
    protected function prettyItems(int $items)
    {
        return number_format($items, 0, ',', ' ') . ' ' .
            Lang::choice('novaStorageInfoCard.values', $items);
    }

    /**
     * Just prettify digging into object.
     * We can use it by one line, but this isn't clear.
     *
     * @param string $disk
     *
     * @return array
     */
    protected function getClient(string $disk)
    {
        /** @var FilesystemAdapter $fs */
        $fs = Storage::disk($disk);

        /** @var Filesystem $driver */
        $driver = $fs->getDriver();

        /** @var AwsS3Adapter $adapter */
        $adapter = $driver->getAdapter();

        return [
            $adapter->getClient(), $adapter->getBucket(),
        ];
    }

    /**
     * @param string $disk
     *
     * @return string
     */
    protected function getKey(string $disk)
    {
        return 'qubeek-nova-storage-info-card-' . $disk;
    }

    public function lang()
    {
        return Lang::get('*');
    }
}
