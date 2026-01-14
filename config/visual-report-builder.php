<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Visual Report Builder Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all settings for the Visual Report Builder
    | package. Customize these values to match your application requirements.
    |
    */

    // Route prefix
    'prefix' => env('VISUAL_REPORT_PREFIX', 'visual-reports'),

    // Middleware to apply to routes
    'middleware' => ['web', 'auth'],

    // API middleware
    'api_middleware' => ['api', 'auth:sanctum'],

    // Enable/disable specific exporters
    'exporters' => [
        'csv' => true,
        'excel' => true,
        'pdf' => true,
        'json' => true,
    ],

    // Caching configuration
    'cache' => [
        'enabled' => env('VISUAL_REPORT_CACHE_ENABLED', true),
        'ttl' => env('VISUAL_REPORT_CACHE_TTL', 3600), // 1 hour
        'store' => env('VISUAL_REPORT_CACHE_STORE', 'default'),
    ],

    // Model auto-discovery
    'models' => [
        'auto_discover' => env('VISUAL_REPORT_AUTO_DISCOVER', true),
        'namespace' => env('VISUAL_REPORT_MODEL_NAMESPACE', 'App\\Models'),
        'path' => env('VISUAL_REPORT_MODEL_PATH', app_path('Models')),
    ],

    // Authentication configuration
    'auth' => [
        'guard' => env('VISUAL_REPORT_AUTH_GUARD', 'web'),
        'verify_ownership' => env('VISUAL_REPORT_VERIFY_OWNERSHIP', true),
    ],

    // Export configuration
    'export' => [
        'disk' => env('VISUAL_REPORT_EXPORT_DISK', 'local'),
        'path' => env('VISUAL_REPORT_EXPORT_PATH', 'exports'),
        'max_rows' => env('VISUAL_REPORT_MAX_EXPORT_ROWS', 100000),
    ],

    // Pivot table configuration
    'pivot' => [
        'max_dimensions' => env('VISUAL_REPORT_MAX_DIMENSIONS', 5),
        'max_metrics' => env('VISUAL_REPORT_MAX_METRICS', 10),
        'include_totals' => env('VISUAL_REPORT_INCLUDE_TOTALS', true),
    ],

    // UI configuration
    'ui' => [
        'theme' => env('VISUAL_REPORT_THEME', 'light'),
        'items_per_page' => env('VISUAL_REPORT_ITEMS_PER_PAGE', 50),
        'max_filters' => env('VISUAL_REPORT_MAX_FILTERS', 10),
    ],

    // File size limits
    'limits' => [
        'max_query_timeout' => env('VISUAL_REPORT_MAX_QUERY_TIMEOUT', 300), // 5 minutes
        'max_export_size' => env('VISUAL_REPORT_MAX_EXPORT_SIZE', 104857600), // 100MB
    ],

    // Logging
    'logging' => [
        'enabled' => env('VISUAL_REPORT_LOGGING_ENABLED', true),
        'channel' => env('VISUAL_REPORT_LOG_CHANNEL', 'stack'),
    ],

    // Features
    'features' => [
        'templates' => true,
        'sharing' => true,
        'scheduling' => false,
        'notifications' => false,
    ],

    // Permissions configuration
    'permissions' => [
        // Who can create templates using the builder
        // Options: 'all' (everyone), 'admin' (only admins), or role name
        'create_templates' => env('VISUAL_REPORT_CREATE_TEMPLATES', 'all'),

        // Who can create saved reports
        'create_reports' => env('VISUAL_REPORT_CREATE_REPORTS', 'all'),

        // Who can share reports
        'share_reports' => env('VISUAL_REPORT_SHARE_REPORTS', 'all'),

        // Who can export reports
        'export_reports' => env('VISUAL_REPORT_EXPORT_REPORTS', 'all'),
    ],
];
