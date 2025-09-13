<?php

return [
    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', '')),
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    //// AspirePress Configuration

    'aspirecloud' => [
        'download' => [
            'base' => env('DOWNLOAD_BASE', env('APP_URL') . '/download/'), # must have a trailing slash!
            'cache_seconds' => env('DOWNLOAD_CACHE_SECONDS', 60 * 60 * 24 * 10),
        ],
    ],
];
