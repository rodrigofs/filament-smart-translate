<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Translation System Enabled
    |--------------------------------------------------------------------------
    | Enable or disable the entire auto-translation system
    */
    'enabled' => env('FILAMENT_SMART_TRANSLATE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Component-Specific Settings
    |--------------------------------------------------------------------------
    | Fine-grained control over which components should be auto-translated
    */
    'components' => [
        'resources' => [
            'enabled' => true,
            'fallback_strategy' => 'original', // humanize, original, lower_case
        ],
        'navigations' => [
            'enabled' => true,
            'fallback_strategy' => 'original',
        ],
        'actions' => [
            'enabled' => true,
            'fallback_strategy' => 'original',
        ],
        'clusters' => [
            'enabled' => true,
            'fallback_strategy' => 'original',
        ],
        'pages' => [
            'enabled' => true,
            'fallback_strategy' => 'original',
        ],
        'fields' => [
            'enabled' => true,
            'fallback_strategy' => 'original',
        ],
        'schemas' => [
            'enabled' => true,
            'fallback_strategy' => 'original',
        ],
        'entries' => [
            'enabled' => true,
            'fallback_strategy' => 'original',
        ],
        'columns' => [
            'enabled' => true,
            'fallback_strategy' => 'original',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Strategies
    |--------------------------------------------------------------------------
    | Define custom fallback strategies. Built-in strategies (humanize,
    | original, title_case) are available by default.
    */
    'fallback_strategies' => [
        // 'custom_strategy' => \App\Strategies\CustomFallbackStrategy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Settings
    |--------------------------------------------------------------------------
    | Logging and debugging options
    */
    'debug' => [
        'log_missing_translations' => env('FILAMENT_SMART_TRANSLATE_DEBUG', false),
        'log_fallback_usage' => env('FILAMENT_SMART_TRANSLATE_DEBUG', false),
    ],
];
