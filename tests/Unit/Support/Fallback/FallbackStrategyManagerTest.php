<?php

use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyInterface;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyManager;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\HumanizeStrategy;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\OriginalStrategy;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\TitleCaseStrategy;

beforeEach(function () {
    // Clear strategy cache between tests
    $reflection = new ReflectionClass(FallbackStrategyManager::class);
    $strategiesProperty = $reflection->getProperty('strategies');
    $strategiesProperty->setAccessible(true);
    $strategiesProperty->setValue([]);

    Config::set('filament-smart-translate.fallback_strategies', []);
});

it('resolves built-in humanize strategy', function () {
    $strategy = FallbackStrategyManager::resolve('humanize');

    expect($strategy)->toBeInstanceOf(HumanizeStrategy::class);
    expect($strategy->apply('user_name'))->toBe('User_Name');
});

it('resolves built-in original strategy', function () {
    $strategy = FallbackStrategyManager::resolve('original');

    expect($strategy)->toBeInstanceOf(OriginalStrategy::class);
    expect($strategy->apply('user_name'))->toBe('user_name');
});

it('resolves built-in title_case strategy', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    expect($strategy)->toBeInstanceOf(TitleCaseStrategy::class);
    expect($strategy->apply('user name'))->toBe('User Name');
});

it('caches resolved strategies', function () {
    $strategy1 = FallbackStrategyManager::resolve('humanize');
    $strategy2 = FallbackStrategyManager::resolve('humanize');

    expect($strategy1)->toBe($strategy2);
});

it('resolves custom closure strategies from configuration', function () {
    Config::set('filament-smart-translate.fallback_strategies.custom', function ($key) {
        return 'CUSTOM_' . strtoupper($key);
    });

    $strategy = FallbackStrategyManager::resolve('custom');

    expect($strategy)->toBeInstanceOf(FallbackStrategyInterface::class);
    expect($strategy->apply('test'))->toBe('CUSTOM_TEST');
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

it('falls back to humanize strategy for unknown strategies', function () {
    $strategy = FallbackStrategyManager::resolve('unknown_strategy');

    expect($strategy)->toBeInstanceOf(HumanizeStrategy::class);
    expect($strategy->apply('test_key'))->toBe('Test_Key');
});

it('handles non-callable strategies in configuration', function () {
    Config::set('filament-smart-translate.fallback_strategies.invalid', 'not_callable');

    $strategy = FallbackStrategyManager::resolve('invalid');

    expect($strategy)->toBeInstanceOf(HumanizeStrategy::class);
    expect($strategy->apply('test_key'))->toBe('Test_Key');
});

it('throws exception if humanize strategy cannot be resolved', function () {
    // Mock the getBuiltInStrategy method to throw exception for humanize
    $manager = new class extends FallbackStrategyManager
    {
        protected static function getBuiltInStrategy(string $strategy): string
        {
            throw new InvalidArgumentException("Unknown fallback strategy: {$strategy}");
        }
    };

    expect(fn () => $manager::resolve('humanize'))
        ->toThrow(InvalidArgumentException::class, 'Could not resolve humanize fallback strategy');
});

it('applies strategy directly without resolving twice', function () {
    Config::set('filament-smart-translate.fallback_strategies.custom', function ($key) {
        return 'APPLIED_' . $key;
    });

    $result = FallbackStrategyManager::apply('test', 'custom');

    expect($result)->toBe('APPLIED_test');
});

it('handles class-not-exists scenario gracefully', function () {
    Config::set('filament-smart-translate.fallback_strategies.missing_class', 'NonExistentClass');

    $strategy = FallbackStrategyManager::resolve('missing_class');

    expect($strategy)->toBeInstanceOf(HumanizeStrategy::class);
    expect($strategy->apply('test'))->toBe('Test');
});

it('resolves different built-in strategies correctly', function () {
    $humanizeStrategy = FallbackStrategyManager::resolve('humanize');
    $originalStrategy = FallbackStrategyManager::resolve('original');
    $titleCaseStrategy = FallbackStrategyManager::resolve('title_case');

    expect($humanizeStrategy->apply('user_name'))->toBe('User_Name');
    expect($originalStrategy->apply('user_name'))->toBe('user_name');
    expect($titleCaseStrategy->apply('user name'))->toBe('User Name');
});
