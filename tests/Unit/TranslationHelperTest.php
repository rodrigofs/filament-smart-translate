<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyManager;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

beforeEach(function () {
    FallbackStrategyManager::clearCache();
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);
    Config::set('filament-smart-translate.debug.log_missing_translations', false);
    Config::set('filament-smart-translate.fallback_strategies', []);
});

it('returns empty string for empty key', function () {
    $result = TranslationHelper::translateWithFallback('');
    expect($result)->toBe('');
});

it('returns fallback when translation system is disabled', function () {
    Config::set('filament-smart-translate.enabled', false);

    $result = TranslationHelper::translateWithFallback('test.key');
    expect($result)->toBe('Key');
});

it('returns fallback when component is disabled', function () {
    Config::set('filament-smart-translate.components.test_component.enabled', false);

    $result = TranslationHelper::translateWithFallback('test.key', 'test_component');
    expect($result)->toBe('Key');
});

it('uses component strategy when component is enabled', function () {
    Config::set('filament-smart-translate.components.test_component.enabled', true);
    Config::set('filament-smart-translate.components.test_component.fallback_strategy', 'humanize');

    $result = TranslationHelper::translateWithFallback('test_key', 'test_component');
    expect($result)->toBe('Test Key');
});

it('uses default component values when not configured', function () {
    $result = TranslationHelper::translateWithFallback('test_key', 'undefined_component');
    expect($result)->toBe('Test key'); // original strategy default
});

it('logs missing translation when debug is enabled and component is disabled', function () {
    Config::set('filament-smart-translate.debug.log_missing_translations', true);
    Config::set('filament-smart-translate.components.test_component.enabled', false);

    Log::shouldReceive('info')
        ->once()
        ->with('Filament Smart Translation: Missing translation', [
            'key' => 'test_key',
            'component' => 'test_component',
            'fallback_strategy' => 'original',
            'locale' => 'pt_BR',
        ]);

    TranslationHelper::translateWithFallback('test_key', 'test_component');
});

it('does not log when debug is disabled', function () {
    Config::set('filament-smart-translate.debug.log_missing_translations', false);

    Log::shouldReceive('info')->never();

    TranslationHelper::translateWithFallback('test_key', 'test_component');
});

it('handles exceptions in strategy application gracefully', function () {
    // Force a strategy manager exception
    Config::set('filament-smart-translate.fallback_strategies.bad_strategy', 'NonExistentClass');
    Config::set('filament-smart-translate.components.test_component.fallback_strategy', 'bad_strategy');

    $result = TranslationHelper::translateWithFallback('test.key', 'test_component');

    // Should return safe extraction even if strategy fails
    expect($result)->toBe('Key');
});

it('handles unknown strategies gracefully', function () {
    Config::set('filament-smart-translate.components.test_component.enabled', true);
    Config::set('filament-smart-translate.components.test_component.fallback_strategy', 'completely_unknown_strategy');

    $result = TranslationHelper::translateWithFallback('test.key', 'test_component');

    // Should return safe extraction using original strategy
    expect($result)->toBe('Key');
});

it('handles logging gracefully when log fails', function () {
    Config::set('filament-smart-translate.debug.log_missing_translations', true);
    Config::set('filament-smart-translate.components.test_component.enabled', false);

    // Even if logging fails internally, the method should still work
    $result = TranslationHelper::translateWithFallback('test_key', 'test_component');
    expect($result)->toBe('Test key');
});

it('handles invalid config values gracefully', function () {
    // Test with invalid config values that might cause issues
    Config::set('filament-smart-translate.components.test_component.enabled', 'invalid_boolean');
    Config::set('filament-smart-translate.components.test_component.fallback_strategy', null);

    $result = TranslationHelper::translateWithFallback('test_key', 'test_component');

    // Should still return a result
    expect($result)->toBe('Test key');
});

it('handles edge cases with special characters', function () {
    $result1 = TranslationHelper::translateWithFallback('test@key');
    $result2 = TranslationHelper::translateWithFallback('test-key');
    $result3 = TranslationHelper::translateWithFallback('test_key');

    expect($result1)->toBe('Test@key');
    expect($result2)->toBe('Test key'); // kebab() converts hyphens to spaces
    expect($result3)->toBe('Test key');
});

it('handles keys without dots correctly', function () {
    $result = TranslationHelper::translateWithFallback('singlekey');
    expect($result)->toBe('Singlekey');
});

it('handles complex dotted keys correctly', function () {
    $result = TranslationHelper::translateWithFallback('namespace.group.subgroup.final_key');
    expect($result)->toBe('Final key');
});

it('handles different strategies correctly', function () {
    Config::set('filament-smart-translate.components.test.enabled', true);

    // Test humanize strategy
    Config::set('filament-smart-translate.components.test.fallback_strategy', 'humanize');
    $result1 = TranslationHelper::translateWithFallback('user_profile_name', 'test');
    expect($result1)->toBe('User Profile Name');

    // Test original strategy
    Config::set('filament-smart-translate.components.test.fallback_strategy', 'original');
    $result2 = TranslationHelper::translateWithFallback('user_profile_name', 'test');
    expect($result2)->toBe('User profile name');

    // Test lower_case strategy (converts underscores to hyphens and lowercases)
    Config::set('filament-smart-translate.components.test.fallback_strategy', 'lower_case');
    $result3 = TranslationHelper::translateWithFallback('user_profile_name', 'test');
    expect($result3)->toBe('user-profile-name');
});

it('handles boolean component enabled config correctly', function () {
    // Test with true
    Config::set('filament-smart-translate.components.test.enabled', true);
    $result1 = TranslationHelper::translateWithFallback('test_key', 'test');
    expect($result1)->toBe('Test key');

    // Test with false
    Config::set('filament-smart-translate.components.test.enabled', false);
    $result2 = TranslationHelper::translateWithFallback('test_key', 'test');
    expect($result2)->toBe('Test key'); // Uses original strategy for disabled component

    // Test with non-boolean (should use default enabled = true)
    Config::set('filament-smart-translate.components.test.enabled', 'string_value');
    $result3 = TranslationHelper::translateWithFallback('test_key', 'test');
    expect($result3)->toBe('Test key');
});

it('uses correct fallback strategy from component config', function () {
    Config::set('filament-smart-translate.components.test.enabled', true);
    Config::set('filament-smart-translate.components.test.fallback_strategy', 'humanize');

    $result = TranslationHelper::translateWithFallback('user_name', 'test');
    expect($result)->toBe('User Name');
});

it('uses default strategy when no fallback strategy is configured', function () {
    Config::set('filament-smart-translate.components.test.enabled', true);
    // No fallback_strategy configured

    $result = TranslationHelper::translateWithFallback('user_name', 'test');
    expect($result)->toBe('User name'); // Should use default 'original' strategy
});
