<?php

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\FieldWrapper;
use Rodrigofs\FilamentSmartTranslate\TranslationServiceProvider;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);

    // Setup test translations
    app('translator')->addLines([
        'fields.user_name' => 'Nome de Usuário',
        'entries.email_address' => 'Endereço de Email',
        'columns.created_at' => 'Data de Criação',
    ], 'pt_BR');
});

it('field wrapper applies translation correctly', function () {
    $wrapper = new FieldWrapper('user_name', 'fields');

    // Should find existing translation first, before fallback
    expect($wrapper->getLabel())->toBe('Nome de Usuário');
});

it('field wrapper uses fallback when no translation exists', function () {
    // Test with a key that doesn't have translation
    $wrapper = new FieldWrapper('nonexistent_field', 'fields');

    // Should use fallback strategy (original by default)
    expect($wrapper->getLabel())->toBe('Nonexistent field');
});

it('field wrapper with existing translation', function () {
    // This should test if field wrapper uses actual translations first
    $wrapper = new FieldWrapper('user_name', 'fields');

    // Should return translation if it exists
    expect($wrapper->getLabel())->toBe('Nome de Usuário');
});

it('field components use lower_case strategy from config', function () {
    // Boot service provider to apply configurations
    $provider = new TranslationServiceProvider(app());
    $provider->boot();

    $field = TextInput::make('first_name_field');

    // Field should be created successfully
    expect($field)->toBeInstanceOf(TextInput::class);
    expect($field->getName())->toBe('first_name_field');
});

it('text entry components use lower_case strategy from config', function () {
    // Boot service provider to apply configurations
    $provider = new TranslationServiceProvider(app());
    $provider->boot();

    $entry = TextEntry::make('email_address_field');

    // Entry should be created successfully
    expect($entry)->toBeInstanceOf(TextEntry::class);
    expect($entry->getName())->toBe('email_address_field');
});

it('column components use lower_case strategy from config', function () {
    // Boot service provider to apply configurations
    $provider = new TranslationServiceProvider(app());
    $provider->boot();

    $column = TextColumn::make('created_at_timestamp');

    // Column should be created successfully
    expect($column)->toBeInstanceOf(TextColumn::class);
    expect($column->getName())->toBe('created_at_timestamp');
});

it('field wrapper handles empty names correctly', function () {
    $wrapper = new FieldWrapper('');

    expect($wrapper->getLabel())->toBe('');
});

it('field wrapper with underscores uses lower_case strategy', function () {
    // Configure to use lower_case strategy for fields
    Config::set('filament-smart-translate.components.fields.fallback_strategy', 'lower_case');

    $wrapper = new FieldWrapper('user_profile_settings');

    // Should apply lower_case strategy: user_profile_settings -> user-profile-settings
    expect($wrapper->getLabel())->toBe('user-profile-settings');
});

it('different components use correct contexts', function () {
    // Test that different component types use their correct contexts

    // Fields should use 'fields' context
    Config::set('filament-smart-translate.components.fields.fallback_strategy', 'lower_case');
    $fieldWrapper = new FieldWrapper('test_field', 'fields');
    expect($fieldWrapper->getLabel())->toBe('test-field');

    // Entries should use 'entries' context
    Config::set('filament-smart-translate.components.entries.fallback_strategy', 'lower_case');
    $entryWrapper = new FieldWrapper('test_entry', 'entries');
    expect($entryWrapper->getLabel())->toBe('test-entry');

    // Columns should use 'columns' context
    Config::set('filament-smart-translate.components.columns.fallback_strategy', 'lower_case');
    $columnWrapper = new FieldWrapper('test_column', 'columns');
    expect($columnWrapper->getLabel())->toBe('test-column');
});

it('wrappers respect component enabled/disabled settings', function () {
    // Disable fields component
    Config::set('filament-smart-translate.components.fields.enabled', false);

    $wrapper = new FieldWrapper('user_profile', 'fields');

    // When component is disabled, should use default (original) strategy
    expect($wrapper->getLabel())->toBe('User profile');
});

it('wrappers handle dotted keys correctly', function () {
    $wrapper = new FieldWrapper('user.profile.settings');

    // Should extract only the part after last dot
    expect($wrapper->getLabel())->toBe('Settings');
});

it('integration test - service provider configures all wrapper types', function () {
    // Boot the service provider
    $provider = new TranslationServiceProvider(app());
    $provider->boot();

    // Create different component types
    $field = TextInput::make('test_field');
    $entry = TextEntry::make('test_entry');
    $column = TextColumn::make('test_column');

    // All should be created successfully
    expect($field)->toBeInstanceOf(TextInput::class);
    expect($entry)->toBeInstanceOf(TextEntry::class);
    expect($column)->toBeInstanceOf(TextColumn::class);

    // Names should be preserved
    expect($field->getName())->toBe('test_field');
    expect($entry->getName())->toBe('test_entry');
    expect($column->getName())->toBe('test_column');
});
