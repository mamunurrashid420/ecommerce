<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SiteSettings extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'site-settings';
    }
}