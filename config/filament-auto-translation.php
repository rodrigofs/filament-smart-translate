<?php

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
        'humanize' => Rodrigofs\FilamentAutoTranslate\Support\Fallback\HumanizeStrategy::class,
        'original' => Rodrigofs\FilamentAutoTranslate\Support\Fallback\OriginalStrategy::class,
        'title_case' => Rodrigofs\FilamentAutoTranslate\Support\Fallback\TitleCaseStrategy::class,
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
