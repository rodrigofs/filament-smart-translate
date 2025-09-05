<?php

use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);

    // Set up test translations
    app('translator')->addLines([
        'resource_labels.user' => 'Usuário',
        'navigation.admin' => 'Administração',
        'actions.create' => 'Criar',
    ], 'pt_BR');
});

it('translates existing keys', function () {
    $result = TranslationHelper::translateWithFallback('user', 'resource_labels');

    expect($result)->toBe('Usuário');
});

it('respects disabled setting', function () {
    Config::set('filament-smart-translate.enabled', false);

    $result = TranslationHelper::translateWithFallback('user', 'resource_labels');

    expect($result)->toBe('user');
});

it('uses fallback strategies', function () {
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'title_case');

    $result = TranslationHelper::translateWithFallback('user profile', 'resource_labels');

    expect($result)->toBe('User Profile');
});

it('handles empty keys', function () {
    $result = TranslationHelper::translateWithFallback('', 'resource_labels');

    expect($result)->toBe('');
});

it('uses original fallback by default', function () {
    $result = TranslationHelper::translateWithFallback('nonexistent_key', 'resource_labels');

    expect($result)->toBe('nonexistent_key');
});

it('applies humanize fallback strategy', function () {
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'humanize');

    $result = TranslationHelper::translateWithFallback('user_profile_settings', 'resource_labels');

    expect($result)->toBe('User_Profile_Settings');
});

it('respects component disabled setting', function () {
    Config::set('filament-smart-translate.components.resource_labels.enabled', false);

    $result = TranslationHelper::translateWithFallback('user', 'resource_labels');

    expect($result)->toBe('user');
});
