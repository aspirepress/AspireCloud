<?php
declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | CVE Labeller API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the CVE Labeller API integration that provides
    | vulnerability scanning for FAIR Package Manager packages.
    |
    */

    // Enable/disable CVE scanning
    'enabled' => env('CVE_LABELLER_ENABLED', true),

    // API connection settings
    'api_url' => env('CVE_LABELLER_API_URL', 'http://api.cve-labeller.local/api/query'),
    'api_timeout' => (int) env('CVE_LABELLER_API_TIMEOUT', 30),

    // Batch processing settings
    'batch_size' => (int) env('CVE_LABELLER_BATCH_SIZE', 50),
    'batch_delay' => (int) env('CVE_BATCH_DELAY', 0), // milliseconds

    // Retry configuration
    'retry_attempts' => (int) env('CVE_RETRY_ATTEMPTS', 3),
    'retry_delay' => (int) env('CVE_RETRY_DELAY', 5), // seconds
    'retry_multiplier' => (float) env('CVE_RETRY_MULTIPLIER', 2.0),

    // Scheduler intervals (in minutes)
    'schedules' => [
        'high_severity' => (int) env('CVE_HIGH_SEVERITY_INTERVAL', 10),
        'medium_severity' => (int) env('CVE_MEDIUM_SEVERITY_INTERVAL', 30),
        'low_severity' => (int) env('CVE_LOW_SEVERITY_INTERVAL', 60),
        'no_severity' => (int) env('CVE_NO_SEVERITY_INTERVAL', 120),
        'daily_scan_time' => env('CVE_DAILY_SCAN_TIME', '03:00'),
        'daily_scan_enabled' => (bool) env('CVE_DAILY_SCAN_ENABLED', true),
    ],

    // Logging configuration
    'log_level' => env('CVE_LOG_LEVEL', 'info'),
    'log_api_requests' => (bool) env('CVE_LOG_API_REQUESTS', false),
    'log_queries' => (bool) env('CVE_LOG_QUERIES', false),

    // Notification settings
    'notifications' => [
        'email' => [
            'enabled' => (bool) env('CVE_EMAIL_ALERTS_ENABLED', false),
            'recipients' => array_filter(explode(',', env('CVE_ALERT_EMAILS', ''))),
        ],
        'slack' => [
            'enabled' => (bool) env('CVE_SLACK_ENABLED', false),
            'webhook_url' => env('CVE_SLACK_WEBHOOK_URL'),
        ],
    ],

    // Performance settings
    'max_concurrent_requests' => (int) env('CVE_MAX_CONCURRENT_REQUESTS', 5),
    'cache_ttl' => (int) env('CVE_CACHE_TTL', 60), // minutes, 0 = disabled

    // Data retention
    'retention_days' => (int) env('CVE_RETENTION_DAYS', 365), // 0 = keep all
];
