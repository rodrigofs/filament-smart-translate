<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);

    // Set up test translations
    app('translator')->addLines([
        'resource_labels.user' => 'Usuário',
        'actions.create' => 'Criar',
        'test.direct_key' => 'Tradução Direta',
    ], 'pt_BR');
});

it('translates with fallback when component prefix translation exists', function () {
    $result = TranslationHelper::translateWithFallback('user', 'resource_labels');
    expect($result)->toBe('Usuário');
});

it('translates with fallback when direct translation exists', function () {
    $result = TranslationHelper::translateWithFallback('test.direct_key', 'nonexistent_component');
    expect($result)->toBe('Tradução Direta');
});

it('applies custom fallback strategy from configuration', function () {
    Config::set('filament-smart-translate.fallback_strategies.custom_test', fn ($key) => 'CUSTOM_' . strtoupper($key));
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'custom_test');

    $result = TranslationHelper::translateWithFallback('unknown_key', 'resource_labels');
    expect($result)->toBe('CUSTOM_UNKNOWN_KEY');
});

it('applies default fallback strategy when configured strategy is not callable', function () {
    Config::set('filament-smart-translate.fallback_strategies.invalid_strategy', 'not_callable');
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'invalid_strategy');

    $result = TranslationHelper::translateWithFallback('test_key', 'resource_labels');
    expect($result)->toBe('Test_Key'); // Default humanize strategy
});

it('applies match statement fallback strategies', function () {
    // Test humanize strategy
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'humanize');
    $result1 = TranslationHelper::translateWithFallback('user_profile_data', 'resource_labels');
    expect($result1)->toBe('User_Profile_Data');

    // Test title_case strategy
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'title_case');
    $result2 = TranslationHelper::translateWithFallback('user profile data', 'resource_labels');
    expect($result2)->toBe('User Profile Data');

    // Test original strategy
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'original');
    $result3 = TranslationHelper::translateWithFallback('test_key', 'resource_labels');
    expect($result3)->toBe('test_key');

    // Test default case (unknown strategy)
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'unknown_strategy');
    $result4 = TranslationHelper::translateWithFallback('another_test', 'resource_labels');
    expect($result4)->toBe('Another_Test'); // Default to humanize
});

it('logs missing translation when debug is enabled', function () {
    Config::set('filament-smart-translate.debug.log_missing_translations', true);

    Log::shouldReceive('info')
        ->once()
        ->with('Filament Smart Translation: Missing translation', [
            'key' => 'missing_key',
            'component' => 'resource_labels',
            'fallback_strategy' => 'original',
            'locale' => 'pt_BR',
        ]);

    $result = TranslationHelper::translateWithFallback('missing_key', 'resource_labels');
    expect($result)->toBe('missing_key');
});

it('does not log missing translation when debug is disabled', function () {
    Config::set('filament-smart-translate.debug.log_missing_translations', false);

    Log::shouldReceive('info')->never();

    $result = TranslationHelper::translateWithFallback('missing_key', 'resource_labels');
    expect($result)->toBe('missing_key');
});

it('handles component configuration when component does not exist', function () {
    $result = TranslationHelper::translateWithFallback('test_key', 'nonexistent_component');
    expect($result)->toBe('Test_Key'); // Default humanize strategy
});

it('handles component configuration when component is enabled but no fallback strategy defined', function () {
    Config::set('filament-smart-translate.components.test_component', ['enabled' => true]);

    $result = TranslationHelper::translateWithFallback('test_key', 'test_component');
    expect($result)->toBe('Test_Key'); // Default humanize strategy
});

it('handles empty fallback strategies configuration', function () {
    Config::set('filament-smart-translate.fallback_strategies', []);
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'humanize');

    $result = TranslationHelper::translateWithFallback('test_key', 'resource_labels');
    expect($result)->toBe('Test_Key'); // Uses match statement default
});

it('translates with replace parameters', function () {
    app('translator')->addLines([
        'messages.welcome' => 'Bem-vindo, :name!',
    ], 'pt_BR');

    $result = TranslationHelper::translateWithFallback('welcome', 'messages', ['name' => 'João']);
    expect($result)->toBe('Bem-vindo, João!');
});

it('translates with specific locale parameter', function () {
    app('translator')->addLines([
        'resource_labels.user' => 'User',
    ], 'en');

    $result = TranslationHelper::translateWithFallback('user', 'resource_labels', [], 'en');
    expect($result)->toBe('User');
});

it('handles default component parameter', function () {
    app('translator')->addLines([
        'test.test_key' => 'Chave de Teste',
    ], 'pt_BR');

    $result = TranslationHelper::translateWithFallback('test.test_key', 'default');
    expect($result)->toBe('Chave de Teste');
});

it('handles translation with empty key', function () {
    $result = TranslationHelper::translateWithFallback('', 'resource_labels');
    expect($result)->toBe('');
});

it('uses title_case fallback strategy from match statement', function () {
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'title_case');
    Config::set('filament-smart-translate.fallback_strategies', []); // Empty to force match statement

    $result = TranslationHelper::translateWithFallback('user profile data', 'resource_labels');
    expect($result)->toBe('User Profile Data');
});

it('uses original fallback strategy from match statement', function () {
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'original');
    Config::set('filament-smart-translate.fallback_strategies', []); // Empty to force match statement

    $result = TranslationHelper::translateWithFallback('test_key_original', 'resource_labels');
    expect($result)->toBe('test_key_original');
});
