<?php

namespace Qubeek\NovaStorageInfoCard;

use Laravel\Nova\Card;

class NovaStorageInfoCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/3';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'nova-storage-info-card';
    }

    /**
     * Add disks to work with them.
     *
     * @param array $disks
     *
     * @return NovaStorageInfoCard
     */
    public function disks(array $disks)
    {
        return $this->withMeta([
            'disks' => $disks,
        ]);
    }

    /**
     * Pass time for caching data.
     *
     * @param int $seconds
     *
     * @return NovaStorageInfoCard
     */
    public function cacheFor(int $seconds)
    {
        return $this->withMeta([
            'cacheFor' => $seconds
        ]);
    }

    /**
     * Enable or disable autoplay.
     *
     * @param bool $value
     *
     * @return NovaStorageInfoCard
     */
    public function autoplay($value = true)
    {
        return $this->withMeta([
            'autoplay' => $value
        ]);
    }
}
