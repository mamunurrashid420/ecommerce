<?php

namespace App\Helpers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSettingsHelper
{
    /**
     * Get cached site settings
     */
    public static function get($key = null, $default = null)
    {
        $settings = Cache::remember('site_settings', 3600, function () {
            return SiteSetting::getInstance();
        });

        if ($key === null) {
            return $settings;
        }

        return $settings->$key ?? $default;
    }

    /**
     * Clear site settings cache
     */
    public static function clearCache()
    {
        Cache::forget('site_settings');
    }

    /**
     * Get formatted price with currency
     */
    public static function formatPrice($amount)
    {
        $settings = self::get();
        return $settings->formatPrice($amount);
    }

    /**
     * Get site title
     */
    public static function title()
    {
        return self::get('title', 'My Store');
    }

    /**
     * Get site tagline
     */
    public static function tagline()
    {
        return self::get('tagline');
    }

    /**
     * Get contact email
     */
    public static function email()
    {
        return self::get('email');
    }

    /**
     * Get contact number
     */
    public static function phone()
    {
        return self::get('contact_number');
    }

    /**
     * Get business address
     */
    public static function address()
    {
        return self::get('address');
    }

    /**
     * Get header logo URL
     */
    public static function headerLogo()
    {
        $logo = self::get('header_logo');
        return $logo ? Storage::url($logo) : null;
    }

    /**
     * Get footer logo URL
     */
    public static function footerLogo()
    {
        $logo = self::get('footer_logo');
        return $logo ? Storage::url($logo) : null;
    }

    /**
     * Get favicon URL
     */
    public static function favicon()
    {
        $favicon = self::get('favicon');
        return $favicon ? Storage::url($favicon) : null;
    }

    /**
     * Get social media links
     */
    public static function socialLinks()
    {
        return self::get()->social_links_with_defaults;
    }

    /**
     * Get specific social media link
     */
    public static function socialLink($platform)
    {
        $links = self::socialLinks();
        return $links[$platform] ?? null;
    }

    /**
     * Check if store is enabled
     */
    public static function isStoreEnabled()
    {
        return self::get('store_enabled', true);
    }

    /**
     * Get store mode
     */
    public static function storeMode()
    {
        return self::get('store_mode', 'live');
    }

    /**
     * Check if store is in maintenance mode
     */
    public static function isMaintenanceMode()
    {
        return self::storeMode() === 'maintenance';
    }

    /**
     * Get maintenance message
     */
    public static function maintenanceMessage()
    {
        return self::get('maintenance_message', 'We are currently under maintenance. Please check back later.');
    }

    /**
     * Get business hours
     */
    public static function businessHours()
    {
        return self::get()->business_hours_with_defaults;
    }

    /**
     * Check if store is open today
     */
    public static function isOpenToday()
    {
        $today = strtolower(date('l'));
        $hours = self::businessHours();
        
        if (!isset($hours[$today])) {
            return false;
        }

        $todayHours = $hours[$today];
        
        if ($todayHours['closed']) {
            return false;
        }

        $currentTime = date('H:i');
        return $currentTime >= $todayHours['open'] && $currentTime <= $todayHours['close'];
    }

    /**
     * Get currency symbol
     */
    public static function currencySymbol()
    {
        return self::get('currency_symbol', '$');
    }

    /**
     * Get currency code
     */
    public static function currency()
    {
        return self::get('currency', 'USD');
    }

    /**
     * Get shipping cost
     */
    public static function shippingCost()
    {
        return self::get('shipping_cost', 0);
    }

    /**
     * Get free shipping threshold
     */
    public static function freeShippingThreshold()
    {
        return self::get('free_shipping_threshold');
    }

    /**
     * Check if order qualifies for free shipping
     */
    public static function qualifiesForFreeShipping($orderTotal)
    {
        $threshold = self::freeShippingThreshold();
        return $threshold && $orderTotal >= $threshold;
    }

    /**
     * Get tax rate
     */
    public static function taxRate()
    {
        return self::get('tax_rate', 0);
    }

    /**
     * Check if tax is inclusive
     */
    public static function isTaxInclusive()
    {
        return self::get('tax_inclusive', false);
    }

    /**
     * Calculate tax amount
     */
    public static function calculateTax($amount)
    {
        $taxRate = self::taxRate();
        
        if (self::isTaxInclusive()) {
            // Tax is already included in the price
            return $amount * ($taxRate / (100 + $taxRate));
        } else {
            // Tax needs to be added to the price
            return $amount * ($taxRate / 100);
        }
    }

    /**
     * Get meta title
     */
    public static function metaTitle()
    {
        return self::get('meta_title', self::title());
    }

    /**
     * Get meta description
     */
    public static function metaDescription()
    {
        return self::get('meta_description');
    }

    /**
     * Get meta keywords
     */
    public static function metaKeywords()
    {
        return self::get('meta_keywords');
    }

    /**
     * Get Google Analytics ID
     */
    public static function googleAnalyticsId()
    {
        return self::get('google_analytics_id');
    }

    /**
     * Get Facebook Pixel ID
     */
    public static function facebookPixelId()
    {
        return self::get('facebook_pixel_id');
    }

    /**
     * Get payment methods
     */
    public static function paymentMethods()
    {
        return self::get('payment_methods', []);
    }

    /**
     * Get enabled payment methods
     */
    public static function enabledPaymentMethods()
    {
        $methods = self::paymentMethods();
        return array_filter($methods, function ($method) {
            return $method['enabled'] ?? false;
        });
    }

    /**
     * Get shipping methods
     */
    public static function shippingMethods()
    {
        return self::get('shipping_methods', []);
    }

    /**
     * Get enabled shipping methods
     */
    public static function enabledShippingMethods()
    {
        $methods = self::shippingMethods();
        return array_filter($methods, function ($method) {
            return $method['enabled'] ?? false;
        });
    }

    /**
     * Get accepted countries
     */
    public static function acceptedCountries()
    {
        return self::get('accepted_countries', []);
    }

    /**
     * Check if country is accepted
     */
    public static function isCountryAccepted($countryCode)
    {
        $countries = self::acceptedCountries();
        return isset($countries[$countryCode]);
    }
}