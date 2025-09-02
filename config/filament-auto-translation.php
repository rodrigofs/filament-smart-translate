<?php

use Illuminate\Support\Str;

return [
    /*
    |--------------------------------------------------------------------------
    | Translation System Enabled
    |--------------------------------------------------------------------------
    | Enable or disable the entire auto-translation system
    */
    'enabled' => env('FILAMENT_AUTO_TRANSLATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Component-Specific Settings
    |--------------------------------------------------------------------------
    | Fine-grained control over which components should be auto-translated
    */
    'components' => [
        'resource_labels' => [
            'enabled' => true,
            'fallback_strategy' => 'original', // humanize, original, title_case
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
        'navigation_groups' => [
            'enabled' => true,
            'fallback_strategy' => 'original',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Strategies
    |--------------------------------------------------------------------------
    | Define how to handle missing translations
    */
    'fallback_strategies' => [
        'humanize' => fn ($key) => Str::title(Str::snake($key, ' ')),
        'original' => fn ($key) => $key,
        'title_case' => fn ($key) => ucwords($key),
        'custom' => null, // User can define custom closure
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Settings
    |--------------------------------------------------------------------------
    | Logging and debugging options
    */
    'debug' => [
        'log_missing_translations' => env('FILAMENT_AUTO_TRANSLATION_DEBUG', false),
        'log_fallback_usage' => env('FILAMENT_AUTO_TRANSLATION_DEBUG', false),
    ],
];
