<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dropship API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for dropshipping API integration (tmapi.top - Taobao/1688/Tmall)
    |
    */

    // API Base URL (tmapi.top)
    'api_url' => env('DROPSHIP_API_URL', 'http://api.tmapi.top'),

    // API Token (JWT token from tmapi.top)
    'api_token' => env('DROPSHIP_API_TOKEN', ''),

    // Cache timeout in seconds (default: 1 hour)
    'cache_timeout' => env('DROPSHIP_CACHE_TIMEOUT', 3600),

    // Default platform (taobao, 1688, tmall)
    'default_platform' => env('DROPSHIP_DEFAULT_PLATFORM', '1688'),

    // Default language for API responses
    'default_language' => env('DROPSHIP_DEFAULT_LANGUAGE', 'zh-CN'),

    // Default markup percentage for imported products
    'default_markup_percentage' => env('DROPSHIP_DEFAULT_MARKUP', 30),

    // Supported platforms
    'platforms' => [
        'taobao' => [
            'name' => 'Taobao',
            'enabled' => true,
        ],
        '1688' => [
            'name' => '1688',
            'enabled' => true,
        ],
        'tmall' => [
            'name' => 'Tmall',
            'enabled' => true,
        ],
    ],

    // API rate limiting
    'rate_limit' => [
        'requests_per_minute' => env('DROPSHIP_RATE_LIMIT', 60),
    ],
];

