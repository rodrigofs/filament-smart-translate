<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Translation Manager Page
    |--------------------------------------------------------------------------
    |
    | Translation strings for the Translation Manager page interface
    |
    */

    // Page titles and navigation
    'page_title' => 'Manage Translations',
    'navigation_label' => 'Translations',

    // Table columns
    'columns' => [
        'key' => 'Translation Key',
        'value' => 'Translation Value',
        'locale' => 'Locale',
        'category' => 'Category',
        'length' => 'Length',
    ],

    // Actions
    'actions' => [
        'new_translation' => 'New Translation',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'delete_selected' => 'Delete Selected',
        'change_language' => 'Change Language',
        'refresh' => 'Refresh',
        'export' => 'Export',
        'create_backup' => 'Create Backup',
        'statistics' => 'Statistics',
    ],

    // Forms
    'forms' => [
        'language' => 'Language',
        'select_language' => 'Select Language',
        'key' => 'Key',
        'key_placeholder' => 'e.g: resources.user',
        'translation' => 'Translation',
    ],

    // Modals
    'modals' => [
        'delete_translation' => [
            'heading' => 'Delete Translation',
            'description' => 'Are you sure you want to delete this translation? This action cannot be undone.',
        ],
        'delete_selected_translations' => [
            'heading' => 'Delete Selected Translations',
            'description' => 'Are you sure you want to delete the selected translations? This action cannot be undone.',
        ],
        'statistics' => [
            'heading' => 'Translation Statistics',
            'total_translations' => 'Total Translations',
            'empty_translations' => 'Empty Translations',
            'long_translations' => 'Long Translations (>100 chars)',
            'average_length' => 'Average Length',
        ],
    ],

    // Empty state
    'empty_state' => [
        'heading' => 'No translations found',
        'description' => 'Start by adding a new translation using the button above.',
    ],

    // Notifications
    'notifications' => [
        'translation_added' => [
            'title' => 'Translation added',
            'body' => 'The translation \':key\' was added successfully for locale \':locale\'.',
        ],
        'error_adding_translation' => [
            'title' => 'Error adding translation',
            'body' => ':message',
        ],
        'translation_updated' => [
            'title' => 'Translation updated',
            'body' => 'The translation \':key\' was updated successfully.',
        ],
        'error_updating_translation' => [
            'title' => 'Error updating translation',
            'body' => ':message',
        ],
        'translation_deleted' => [
            'title' => 'Translation deleted',
            'body' => 'The translation \':key\' was deleted successfully from locale \':locale\'.',
        ],
        'error_deleting_translation' => [
            'title' => 'Error deleting translation',
            'body' => ':message',
        ],
        'translations_deleted' => [
            'title' => 'Translations deleted',
            'body' => 'Deleted :count translations successfully.',
        ],
        'some_translations_not_deleted' => [
            'title' => 'Some translations were not deleted',
            'body' => 'Failed to delete :count translations.',
        ],
        'error_deleting_translations' => [
            'title' => 'Error deleting translations',
            'body' => ':message',
        ],
        'language_changed' => [
            'title' => 'Language changed',
            'body' => 'Displaying translations for: :locale',
        ],
        'translations_refreshed' => [
            'title' => 'Translations refreshed',
        ],
        'backup_created' => [
            'title' => 'Backup created',
            'body' => 'Backup saved at: :path',
        ],
        'error_creating_backup' => [
            'title' => 'Error creating backup',
            'body' => ':message',
        ],
    ],

    // Validation messages
    'validation' => [
        'key_already_exists' => 'This key already exists for this locale.',
    ],

    // Category colors (used in badges)
    'categories' => [
        'auth' => 'Authentication',
        'validation' => 'Validation',
        'error' => 'Error',
        'ui' => 'User Interface',
        'resources' => 'Resources',
        'navigations' => 'Navigation',
        'actions' => 'Actions',
        'default' => 'General',
    ],
];
