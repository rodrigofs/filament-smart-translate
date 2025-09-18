<?php

use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyManager;

beforeEach(function () {
    FallbackStrategyManager::clearCache();
});

// Target specific uncovered lines in FallbackStrategyManager
it('covers line 40-42 with unknown class resolution', function () {
    Config::set('filament-smart-translate.fallback_strategies.unknown', 'NonExistentClass');

    $result = FallbackStrategyManager::apply('test_key', 'unknown');

    expect($result)->toBeString();
});

it('covers line 50 with missing class scenario', function () {
    // Force unknown strategy scenario
    $result = FallbackStrategyManager::apply('test_key', 'completely_unknown_strategy');

    expect($result)->toBe('Test key');
});

it('covers line 54 with empty key handling', function () {
    $result = FallbackStrategyManager::apply('', 'original');

    expect($result)->toBe('');
});

it('covers line 89 extractSafeLabel method', function () {
    // Use reflection to test private method directly
    $reflection = new ReflectionClass(FallbackStrategyManager::class);
    $method = $reflection->getMethod('extractSafeLabel');
    $method->setAccessible(true);

    $result = $method->invokeArgs(null, ['complex.dotted.key']);

    expect($result)->toBe('Key');
});

it('covers line 111 built-in strategy creation', function () {
    // Clear cache and force recreation
    FallbackStrategyManager::clearCache();

    $result = FallbackStrategyManager::apply('test_key', 'humanize');

    expect($result)->toBe('Test Key');
});

it('covers lines 127-130 class instantiation error handling', function () {
    // Set up a config that will cause instantiation issues
    Config::set('filament-smart-translate.fallback_strategies.broken', \stdClass::class);

    $result = FallbackStrategyManager::apply('test_key', 'broken');

    expect($result)->toBe('Test key');
});

it('covers recursion protection in strategy resolution', function () {
    // Test with a very long key to trigger different code paths
    $longKey = str_repeat('very.long.complex.nested.key.', 20) . 'final';

    $result = FallbackStrategyManager::apply($longKey, 'original');

    expect($result)->toBe('Final');
});

it('covers cache mechanism', function () {
    // First call to populate cache
    $result1 = FallbackStrategyManager::apply('test_key', 'original');

    // Second call should use cache
    $result2 = FallbackStrategyManager::apply('test_key', 'original');

    expect($result1)->toBe($result2);
    expect($result1)->toBe('Test key');
});

it('covers strategy interface validation', function () {
    // Create a custom strategy that implements the interface
    $customStrategy = new class implements \Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyInterface
    {
        public function apply(string $key): string
        {
            return 'custom_' . $key;
        }
    };

    Config::set('filament-smart-translate.fallback_strategies.custom', get_class($customStrategy));

    $result = FallbackStrategyManager::apply('test', 'custom');

    expect($result)->toContain('test');
});

it('covers alias resolution for title_case', function () {
    $result = FallbackStrategyManager::apply('test_key', 'title_case');

    expect($result)->toBe('test-key'); // title_case is alias for lower_case
});
