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
    | Available Locales
    |--------------------------------------------------------------------------
    | Define which locales are available for translation management
    */
    'available_locales' => ['pt_BR', 'en', 'es', 'fr'],

    /*
    |--------------------------------------------------------------------------
    | Translation Management Page Settings
    |--------------------------------------------------------------------------
    |
    | Configure the built-in translation management page that allows you to
    | manage translations through a Filament interface.
    |
    | CONFIGURATION OPTIONS:
    |
    | • enabled: Main switch to enable/disable the entire page
    | • dev_only: Only show the page in development environments (local, development, testing)
    | • navigation.group: Navigation group where the page appears (e.g., 'System', 'Admin')
    | • navigation.label: Label shown in navigation menu
    | • navigation.icon: Heroicon name for the navigation menu
    | • navigation.sort: Sort order in navigation (higher = lower in menu)
    | • page.title: Page title shown in browser tab and page header
    | • page.slug: URL slug for the page (/admin/{slug})
    | • features.backup: Enable/disable backup creation functionality
    | • features.export: Enable/disable export to JSON functionality
    | • features.locale_selector: Enable/disable language switcher
    | • features.statistics: Enable/disable statistics modal
    | • features.bulk_operations: Enable/disable bulk delete operations
    | • middleware: Array of middleware to apply to the page
    | • authorize: Closure that returns boolean for access control
    |
    | EXAMPLES:
    |
    | // Only show in development:
    | 'dev_only' => true,
    |
    | // Custom navigation:
    | 'navigation' => [
    |     'group' => 'Content Management',
    |     'label' => 'Manage Translations',
    |     'icon' => 'heroicon-o-globe-alt',
    |     'sort' => 500,
    | ],
    |
    | // Disable some features:
    | 'features' => [
    |     'backup' => false,
    |     'export' => true,
    |     'locale_selector' => true,
    |     'statistics' => false,
    |     'bulk_operations' => false,
    | ],
    |
    | // Custom authorization:
    | 'authorize' => fn() => auth()->user()?->can('manage-translations'),
    |
    */
    'translation_page' => [
        // Main control - enable/disable the page entirely
        'enabled' => env('FILAMENT_SMART_TRANSLATE_PAGE_ENABLED', true),

        // Only show in development environment
        'dev_only' => env('FILAMENT_SMART_TRANSLATE_PAGE_DEV_ONLY', false),

        // Navigation settings
        'navigation' => [
            'group' => env('FILAMENT_SMART_TRANSLATE_PAGE_GROUP', 'System'),
            'label' => env('FILAMENT_SMART_TRANSLATE_PAGE_NAV_LABEL', 'Translations'),
            'icon' => env('FILAMENT_SMART_TRANSLATE_PAGE_ICON', 'heroicon-o-language'),
            'sort' => env('FILAMENT_SMART_TRANSLATE_PAGE_SORT', 1000),
        ],

        // Page settings
        'page' => [
            'title' => env('FILAMENT_SMART_TRANSLATE_PAGE_TITLE', 'Manage Translations'),
            'slug' => env('FILAMENT_SMART_TRANSLATE_PAGE_SLUG', 'translations'),
        ],

        // Feature toggles
        'features' => [
            'backup' => env('FILAMENT_SMART_TRANSLATE_BACKUP_ENABLED', true),
            'export' => env('FILAMENT_SMART_TRANSLATE_EXPORT_ENABLED', true),
            'locale_selector' => env('FILAMENT_SMART_TRANSLATE_LOCALE_SELECTOR_ENABLED', true),
            'statistics' => env('FILAMENT_SMART_TRANSLATE_STATISTICS_ENABLED', true),
            'bulk_operations' => env('FILAMENT_SMART_TRANSLATE_BULK_OPERATIONS_ENABLED', true),
        ],

        // Access control
        'middleware' => [],
        'authorize' => null, // Callback function or null for no authorization
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
