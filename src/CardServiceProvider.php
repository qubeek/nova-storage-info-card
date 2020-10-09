<?php

namespace Qubeek\NovaStorageInfoCard;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class CardServiceProvider extends ServiceProvider
{
    protected $langVendor = 'lang/vendor/nova-storage-info-card';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslations();
        $this->loadJSONTranslations();

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-storage-info-card', __DIR__.'/../dist/js/card.js');
            Nova::style('nova-storage-info-card', __DIR__.'/../dist/css/card.css');
        });
    }

    /**
     * Register the card's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/nova-storage-info-card')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Load translations.
     */
    protected function loadTranslations()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../resources/lang' => resource_path($this->langVendor)], 'translations');
        } elseif (method_exists('Nova', 'translations')) {
            $locale = app()->getLocale();
            $fallbackLocale = config('app.fallback_locale');

            if ($this->attemptToLoadTranslations($locale, 'project')) {
                return;
            }
            if ($this->attemptToLoadTranslations($locale, 'local')) {
                return;
            }
            if ($this->attemptToLoadTranslations($fallbackLocale, 'project')) {
                return;
            }
            if ($this->attemptToLoadTranslations($fallbackLocale, 'local')) {
                return;
            }
            $this->attemptToLoadTranslations('en', 'local');
        }
    }

    /**
     * Using this method only for working with controller environment.
     */
    protected function loadJSONTranslations()
    {
        if ($this->attemptToLoadJSONTranslations('project')) {
            return;
        }
        if ($this->attemptToLoadJSONTranslations('local')) {
            return;
        }
        if ($this->attemptToLoadJSONTranslations('project')) {
            return;
        }
        if ($this->attemptToLoadJSONTranslations('local')) {
            return;
        }
        $this->attemptToLoadJSONTranslations('local');
    }

    /**
     * @param $locale
     * @param $from
     *
     * @return bool
     */
    protected function attemptToLoadTranslations($locale, $from)
    {
        $filePath = $from === 'local'
            ? __DIR__.'/../resources/lang/'.$locale.'.json'
            : resource_path($this->langVendor).'/'.$locale.'.json';

        $localeFileExists = File::exists($filePath);
        if ($localeFileExists) {
            Nova::translations($filePath);

            return true;
        }

        return false;
    }

    /**
     * @param $from
     *
     * @return bool
     */
    protected function attemptToLoadJSONTranslations($from)
    {
        $filePath = $from === 'local'
            ? __DIR__.'/../resources/lang/'
            : resource_path($this->langVendor).'/';

        $localeFileExists = File::exists($filePath);
        if ($localeFileExists) {
            $this->loadJsonTranslationsFrom($filePath);

            return true;
        }

        return false;
    }
}
