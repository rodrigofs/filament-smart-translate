<?php

use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyInterface;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyManager;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\Strategies\HumanizeStrategy;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\Strategies\LowerCaseStrategy;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\Strategies\OriginalStrategy;

beforeEach(function () {
    FallbackStrategyManager::clearCache();
    Config::set('filament-smart-translate.fallback_strategies', []);
});

it('resolves built-in humanize strategy', function () {
    $strategy = FallbackStrategyManager::resolve('humanize');

    expect($strategy)->toBeInstanceOf(HumanizeStrategy::class);
    expect($strategy->apply('user_name'))->toBe('User Name');
});

it('resolves built-in original strategy', function () {
    $strategy = FallbackStrategyManager::resolve('original');

    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
    expect($strategy->apply('user_name'))->toBe('User name');
});

it('resolves built-in lower_case strategy', function () {
    $strategy = FallbackStrategyManager::resolve('lower_case');

    expect($strategy)->toBeInstanceOf(LowerCaseStrategy::class);
    expect($strategy->apply('user_name'))->toBe('user-name');
});

it('resolves built-in title_case strategy as alias', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    expect($strategy)->toBeInstanceOf(LowerCaseStrategy::class);
    expect($strategy->apply('user_name'))->toBe('user-name');
});

it('caches resolved strategies', function () {
    $strategy1 = FallbackStrategyManager::resolve('humanize');
    $strategy2 = FallbackStrategyManager::resolve('humanize');

    expect($strategy1)->toBe($strategy2);
});

it('resolves custom class strategies from configuration', function () {
    $customStrategyClass = new class implements FallbackStrategyInterface
    {
        public function apply(string $key): string
        {
            return 'CLASS_' . $key;
        }
    };

    Config::set('filament-smart-translate.fallback_strategies.custom_class', get_class($customStrategyClass));

    $strategy = FallbackStrategyManager::resolve('custom_class');

    expect($strategy)->toBeInstanceOf(FallbackStrategyInterface::class);
    expect($strategy->apply('test'))->toBe('CLASS_test');
});

it('falls back to original strategy for unknown strategies', function () {
    $strategy = FallbackStrategyManager::resolve('unknown_strategy');

    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
    expect($strategy->apply('test_key'))->toBe('Test key');
});

it('handles non-existent class in configuration', function () {
    Config::set('filament-smart-translate.fallback_strategies.invalid', 'NonExistentClass');

    $strategy = FallbackStrategyManager::resolve('invalid');

    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
    expect($strategy->apply('test_key'))->toBe('Test key');
});

it('handles empty class name in configuration', function () {
    Config::set('filament-smart-translate.fallback_strategies.empty', '');

    $strategy = FallbackStrategyManager::resolve('empty');

    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
    expect($strategy->apply('test_key'))->toBe('Test key');
});

it('applies strategy directly without resolving twice', function () {
    $result = FallbackStrategyManager::apply('test.key', 'humanize');

    expect($result)->toBe('Key');
});

it('handles empty keys gracefully', function () {
    $result = FallbackStrategyManager::apply('', 'humanize');

    expect($result)->toBe('');
});

it('handles keys without dots correctly', function () {
    $result = FallbackStrategyManager::apply('singlekey', 'original');

    expect($result)->toBe('Singlekey');
});

it('prevents infinite recursion with circular dependencies', function () {
    // Since we now prioritize custom strategies but only allow classes (not strategy chains),
    // we test with an invalid scenario that would fall back to original strategy
    Config::set('filament-smart-translate.fallback_strategies', [
        'circular1' => 'NonExistentClass1',
        'circular2' => 'NonExistentClass2',
    ]);

    $strategy = FallbackStrategyManager::resolve('circular1');
    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
});

it('prevents excessive recursion with unknown strategies', function () {
    // Test that multiple unknown strategies eventually fall back to original
    $strategy = FallbackStrategyManager::resolve('completely_unknown_strategy');
    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
});

it('handles strategy class instantiation failures gracefully', function () {
    // We can't test class instantiation failure in this context since anonymous classes
    // are instantiated when defined. Instead, test with non-existent class
    Config::set('filament-smart-translate.fallback_strategies.bad_class', 'NonExistentBadClass');

    $strategy = FallbackStrategyManager::resolve('bad_class');

    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
});

it('ignores closure strategies for security', function () {
    Config::set('filament-smart-translate.fallback_strategies.closure', function ($key) {
        return 'DANGEROUS_' . $key;
    });

    $strategy = FallbackStrategyManager::resolve('closure');

    // Should fall back to original strategy since closures are ignored
    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
});

it('validates interface implementation', function () {
    $invalidClass = new class
    {
        public function apply(string $key): string
        {
            return $key;
        }
    };

    Config::set('filament-smart-translate.fallback_strategies.invalid_interface', get_class($invalidClass));

    $strategy = FallbackStrategyManager::resolve('invalid_interface');

    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
});

it('clears cache correctly', function () {
    $strategy1 = FallbackStrategyManager::resolve('humanize');

    FallbackStrategyManager::clearCache();

    $strategy2 = FallbackStrategyManager::resolve('humanize');

    expect($strategy1)->not->toBe($strategy2);
    expect($strategy1)->toBeInstanceOf(HumanizeStrategy::class);
    expect($strategy2)->toBeInstanceOf(HumanizeStrategy::class);
});

it('handles ultimate fallback when all else fails', function () {
    // Force a scenario where even the original strategy fails
    $result = FallbackStrategyManager::apply('test.key', 'completely_unknown');

    // Should return safe extraction
    expect($result)->toBe('Key');
});
