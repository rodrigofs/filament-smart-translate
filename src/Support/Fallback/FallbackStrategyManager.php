<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentSmartTranslate\Support\Fallback;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\Strategies\HumanizeStrategy;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\Strategies\LowerCaseStrategy;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\Strategies\OriginalStrategy;
use Throwable;

final class FallbackStrategyManager
{
    /**
     * @var array<string, FallbackStrategyInterface>
     */
    private static array $strategies = [];

    /**
     * @var array<string>
     */
    private static array $resolutionStack = [];

    private const SAFE_FALLBACK_STRATEGY = 'original';

    private const MAX_RECURSION_DEPTH = 3;

    public static function apply(string $key, string $strategy): string
    {
        if (empty($key)) {
            return '';
        }

        try {
            $resolvedStrategy = self::resolve($strategy);

            return $resolvedStrategy->apply($key);
        } catch (Throwable) {
            // Ultimate fallback - extract label from key
            return self::extractSafeLabel($key);
        }
    }

    public static function resolve(string $strategy): FallbackStrategyInterface
    {
        // Prevent infinite recursion
        if (in_array($strategy, self::$resolutionStack, true)) {
            throw new InvalidArgumentException("Circular dependency detected in strategy resolution: {$strategy}");
        }

        if (count(self::$resolutionStack) >= self::MAX_RECURSION_DEPTH) {
            throw new InvalidArgumentException("Maximum recursion depth exceeded while resolving strategy: {$strategy}");
        }

        // Return cached strategy if available
        if (isset(self::$strategies[$strategy])) {
            return self::$strategies[$strategy];
        }

        self::$resolutionStack[] = $strategy;

        try {
            $resolvedStrategy = self::resolveStrategy($strategy);
            self::$strategies[$strategy] = $resolvedStrategy;

            return $resolvedStrategy;
        } finally {
            array_pop(self::$resolutionStack);
        }
    }

    private static function resolveStrategy(string $strategy): FallbackStrategyInterface
    {
        // Try custom strategies from config first (only class names, no closures for security)
        $strategies = Config::get('filament-smart-translate.fallback_strategies', []);

        if (isset($strategies[$strategy])) {
            $strategyClass = $strategies[$strategy];

            // Security: Only allow string class names, not closures
            if (is_string($strategyClass) && ! empty($strategyClass) && class_exists($strategyClass)) {
                try {
                    $instance = new $strategyClass();
                    if ($instance instanceof FallbackStrategyInterface) {
                        return $instance;
                    }
                } catch (Throwable) {
                    // Fall through to built-in strategies
                }
            }
        }

        // Try built-in strategies
        try {
            $builtInClass = self::getBuiltInStrategy($strategy);
            if (class_exists($builtInClass)) {
                return new $builtInClass();
            }
        } catch (InvalidArgumentException) {
            // Continue to fallback
        }

        // Use safe fallback strategy if current strategy is not the safe fallback
        if ($strategy !== self::SAFE_FALLBACK_STRATEGY) {
            return self::resolve(self::SAFE_FALLBACK_STRATEGY);
        }

        // Last resort: create a minimal original strategy
        return new OriginalStrategy();
    }

    private static function getBuiltInStrategy(string $strategy): string
    {
        return match ($strategy) {
            'original' => OriginalStrategy::class,
            'humanize' => HumanizeStrategy::class,
            'lower_case' => LowerCaseStrategy::class,
            'title_case' => LowerCaseStrategy::class, // Alias for backwards compatibility
            default => throw new InvalidArgumentException("Unknown built-in strategy: {$strategy}"),
        };
    }

    private static function extractSafeLabel(string $key): string
    {
        return (string) str($key)
            ->afterLast('.')
            ->replace(['-', '_'], ' ')
            ->ucfirst();
    }

    public static function clearCache(): void
    {
        self::$strategies = [];
        self::$resolutionStack = [];
    }
}
