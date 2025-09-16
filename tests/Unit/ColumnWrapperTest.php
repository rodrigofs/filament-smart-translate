<?php

use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\ColumnWrapper;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);

    // Setup test translations
    app('translator')->addLines([
        'columns.created_at' => 'Data de Criação',
        'columns.updated_at' => 'Data de Atualização',
    ], 'pt_BR');
});

it('column wrapper applies translation correctly', function () {
    $wrapper = new ColumnWrapper('created_at');

    // Should find existing translation
    expect($wrapper->getLabel())->toBe('Data de Criação');
});

it('column wrapper uses fallback when no translation exists', function () {
    $wrapper = new ColumnWrapper('nonexistent_column');

    // Should use fallback strategy (original by default)
    expect($wrapper->getLabel())->toBe('Nonexistent column');
});

it('column wrapper handles underscores with lower_case strategy', function () {
    // Configure to use lower_case strategy for columns
    Config::set('filament-smart-translate.components.columns.fallback_strategy', 'lower_case');

    $wrapper = new ColumnWrapper('user_profile_data');

    // Should apply lower_case strategy: user_profile_data -> user-profile-data
    expect($wrapper->getLabel())->toBe('user-profile-data');
});

it('column wrapper handles empty names correctly', function () {
    $wrapper = new ColumnWrapper('');

    expect($wrapper->getLabel())->toBe('');
});

it('column wrapper handles dotted keys correctly', function () {
    $wrapper = new ColumnWrapper('user.profile.settings');

    // Should extract only the part after last dot
    expect($wrapper->getLabel())->toBe('Settings');
});

it('column wrapper respects enabled/disabled settings', function () {
    // Disable columns component
    Config::set('filament-smart-translate.components.columns.enabled', false);

    $wrapper = new ColumnWrapper('test_column');

    // When component is disabled, should use default (original) strategy
    expect($wrapper->getLabel())->toBe('Test column');
});

it('column wrapper handles complex names', function () {
    $wrapper = new ColumnWrapper('first_name_last_name_field');

    // Should use original strategy by default
    expect($wrapper->getLabel())->toBe('First name last name field');
});

it('column wrapper returns correct type', function () {
    $wrapper = new ColumnWrapper('test');

    $label = $wrapper->getLabel();
    expect($label)->toBeString();
});
