# nova-storage-info-card

[![Latest Stable Version](https://poser.pugx.org/qubeek/nova-storage-info-card/v/stable)](https://packagist.org/packages/qubeek/nova-storage-info-card)
[![Total Downloads](https://poser.pugx.org/qubeek/nova-storage-info-card/downloads)](https://packagist.org/packages/qubeek/nova-storage-info-card)
[![License](https://poser.pugx.org/qubeek/nova-storage-info-card/license)](https://packagist.org/packages/qubeek/nova-storage-info-card)
[![composer.lock](https://poser.pugx.org/qubeek/nova-storage-info-card/composerlock)](https://packagist.org/packages/qubeek/nova-storage-info-card)

Get basic information about storage usage. Be sure, that you use **S3 driver for disks, that you adding to that card**

\
\
**Note, that you need to add the disk to config/filesystem.php**

![alt text](screenshots/nova-card.png)

## Installation 

You can install the package into a Laravel app that uses Nova via composer:

```bash
composer require qubeek/nova-storage-info-card
```

## Translation

If you want to override localization, you can publish lang files using that command:

```bash
php artisan vendor:publish --provider="Qubeek\NovaStorageInfoCard\CardServiceProvider"
```

## Usage

Register the card with Nova. To use the package, you need to indicate a disk meta in the format: 

- the name that you used in **config/filesystem.php**
- title for a table (optional).
- rename refresh button

\
For example:
```php
class NovaServiceProvider extends NovaApplicationServiceProvider

...

/**
 * Get the cards that should be displayed on the default Nova dashboard.
 *
 * @return array
 */
protected function cards()
{
    return [
        (new NovaStorageInfoCard())
            ->disks([
                [
                    'title' => 'User uploads',
                    'name' => 'video',
                    'size' => '5 TB'
                ],
                [
                    'title' => 'Test',
                    'name' => 'test',
                    'size' => '5 TB'
                ]
            ])
            ->autoplay(false)
            ->cacheFor(24 * 60 * 60) // Cache for 24 hours
    ];
}
```

## Tested filesystem types

- [Yandex (yandex-object-storage)](https://github.com/fLipE23/yandex-object-storage)
- [Basic S3 driver](https://laravel.com/docs/7.x/filesystem)
- [DigitalOcean space](https://www.digitalocean.com/products/spaces/)


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
