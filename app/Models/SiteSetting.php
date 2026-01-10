<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SiteSetting extends Model
{
    protected $fillable = [
        'title',
        'tagline',
        'description',
        'contact_number',
        'email',
        'support_email',
        'address',
        'business_name',
        'business_registration_number',
        'tax_number',
        'header_logo',
        'footer_logo',
        'favicon',
        'slider_images',
        'offer',
        'social_links',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'currency',
        'currency_symbol',
        'currency_rate',
        'currency_position',
        'price_margin',
        'min_product_quantity',
        'min_order_amount',
        'shipping_cost',
        'shipping_cost_by_ship',
        'shipping_duration_by_ship',
        'shipping_cost_by_air',
        'shipping_duration_by_air',
        'free_shipping_threshold',
        'tax_rate',
        'tax_inclusive',
        'store_enabled',
        'store_mode',
        'maintenance_message',
        'business_hours',
        'payment_methods',
        'shipping_methods',
        'accepted_countries',
        'email_notifications',
        'sms_notifications',
        'notification_email',
        'google_analytics_id',
        'facebook_pixel_id',
        'custom_scripts',
        'terms_of_service',
        'privacy_policy',
        'return_policy',
        'shipping_policy',
        'additional_settings',
    ];

    protected $casts = [
        'social_links' => 'array',
        'slider_images' => 'array',
        'offer' => 'array',
        'business_hours' => 'array',
        'payment_methods' => 'array',
        'shipping_methods' => 'array',
        'accepted_countries' => 'array',
        'additional_settings' => 'array',
        'store_enabled' => 'boolean',
        'tax_inclusive' => 'boolean',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'currency_rate' => 'double',
        'min_product_quantity' => 'integer',
        'min_order_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'shipping_cost_by_ship' => 'decimal:2',
        'shipping_cost_by_air' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    /**
     * Get the singleton instance of site settings
     */
    public static function getInstance()
    {
        return static::first() ?: static::create([
            'title' => 'My Store',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'currency_position' => 'before',
            'store_enabled' => true,
            'store_mode' => 'live',
            'email_notifications' => true,
            'sms_notifications' => false,
            'tax_inclusive' => false,
        ]);
    }

    /**
     * Get formatted currency
     */
    protected function formattedCurrency(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->currency_position === 'before' 
                ? $this->currency_symbol . ' ' 
                : ' ' . $this->currency_symbol
        );
    }

    /**
     * Get social links with defaults
     */
    protected function socialLinksWithDefaults(): Attribute
    {
        return Attribute::make(
            get: fn () => array_merge([
                'facebook' => '',
                'twitter' => '',
                'instagram' => '',
                'linkedin' => '',
                'youtube' => '',
                'tiktok' => '',
                'whatsapp' => '',
            ], $this->social_links ?? [])
        );
    }

    /**
     * Get business hours with defaults
     */
    protected function businessHoursWithDefaults(): Attribute
    {
        return Attribute::make(
            get: fn () => array_merge([
                'monday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'tuesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'wednesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'friday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'saturday' => ['open' => '10:00', 'close' => '16:00', 'closed' => false],
                'sunday' => ['open' => '10:00', 'close' => '16:00', 'closed' => true],
            ], $this->business_hours ?? [])
        );
    }

    /**
     * Get full URL for header logo
     */
    protected function headerLogoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFullUrl($this->header_logo)
        );
    }

    /**
     * Get full URL for footer logo
     */
    protected function footerLogoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFullUrl($this->footer_logo)
        );
    }

    /**
     * Get full URL for favicon
     */
    protected function faviconUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFullUrl($this->favicon)
        );
    }

    /**
     * Get full URLs for slider images with title and subtitle
     */
    protected function sliderImagesUrls(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getSliderImagesUrls()
        );
    }

    /**
     * Get offer with full URL for promotional image
     */
    protected function offerWithUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getOfferWithUrl()
        );
    }

    /**
     * Helper method to get full URLs for slider images
     */
    private function getSliderImagesUrls()
    {
        if (empty($this->slider_images) || !is_array($this->slider_images)) {
            return [];
        }

        return array_map(function ($item) {
            // Handle both old format (string path) and new format (object with image, title, subtitle, hyperlink)
            if (is_string($item)) {
                // Legacy format: just a path string
                return [
                    'image' => $this->getFullUrl($item),
                    'title' => null,
                    'subtitle' => null,
                    'hyperlink' => null,
                ];
            } elseif (is_array($item) && isset($item['image'])) {
                // New format: object with image, title, subtitle, hyperlink
                return [
                    'image' => $this->getFullUrl($item['image'] ?? null),
                    'title' => $item['title'] ?? null,
                    'subtitle' => $item['subtitle'] ?? null,
                    'hyperlink' => $item['hyperlink'] ?? null,
                ];
            }
            
            return [
                'image' => null,
                'title' => null,
                'subtitle' => null,
                'hyperlink' => null,
            ];
        }, $this->slider_images);
    }

    /**
     * Helper method to get offer with full URL for promotional image
     */
    private function getOfferWithUrl()
    {
        if (empty($this->offer) || !is_array($this->offer)) {
            return null;
        }

        return [
            'offer_name' => $this->offer['offer_name'] ?? null,
            'description' => $this->offer['description'] ?? null,
            'amount' => $this->offer['amount'] ?? null,
            'promotional_image' => $this->getFullUrl($this->offer['promotional_image'] ?? null),
            'start_date' => $this->offer['start_date'] ?? null,
            'end_date' => $this->offer['end_date'] ?? null,
        ];
    }

    /**
     * Helper method to get full URL from path
     */
    private function getFullUrl($path)
    {
        if (empty($path)) {
            return null;
        }

        // If URL already starts with http/https, return as is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // If path starts with /storage, prepend the app URL
        if (str_starts_with($path, '/storage')) {
            return config('app.url') . $path;
        }

        // If path doesn't start with /, treat as storage path and prepend
        return config('app.url') . '/storage/' . ltrim($path, '/');
    }

    /**
     * Format price with currency
     */
    public function formatPrice($amount)
    {
        $formatted = number_format($amount, 2);
        
        return $this->currency_position === 'before' 
            ? $this->currency_symbol . $formatted
            : $formatted . $this->currency_symbol;
    }
}