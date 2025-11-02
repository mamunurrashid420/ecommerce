<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Helpers\SiteSettingsHelper;
use Illuminate\Support\ServiceProvider;

class SiteSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the helper as a singleton
        $this->app->singleton('site-settings', function () {
            return new SiteSettingsHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Clear cache when site settings are updated
        SiteSetting::updated(function () {
            SiteSettingsHelper::clearCache();
        });

        SiteSetting::created(function () {
            SiteSettingsHelper::clearCache();
        });
    }
}
