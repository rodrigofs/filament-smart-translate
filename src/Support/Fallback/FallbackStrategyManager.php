<?php

namespace Rodrigofs\FilamentSmartTranslate\Support\Fallback;

use Closure;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class FallbackStrategyManager
{
    /**
     * @var array <string, FallbackStrategyInterface>
     */
    protected static array $strategies = [];

    public static function resolve(string $strategy): FallbackStrategyInterface
    {
        if (isset(static::$strategies[$strategy])) {
            return static::$strategies[$strategy];
        }

        $strategies = Config::get('filament-smart-translate.fallback_strategies', []);

        // Check if it's a closure in configuration
        if (isset($strategies[$strategy]) && is_callable($strategies[$strategy])) {
            return static::$strategies[$strategy] = new class($strategies[$strategy]) implements FallbackStrategyInterface
            {
                public function __construct(private Closure $closure) {}

                public function apply(string $key): string
                {
                    return ($this->closure)($key);
                }
            };
        }

        // Try to get class name
        try {
            $strategyClass = static::getStrategyClass($strategy);

            if (class_exists($strategyClass)) {
                return static::$strategies[$strategy] = new $strategyClass();
            }
        } catch (InvalidArgumentException) {
            // Fall through to default strategy
        }

        // Fallback to default humanize strategy
        if ($strategy !== 'humanize') {
            return static::$strategies[$strategy] = static::resolve('humanize');
        }

        // If we can't resolve humanize itself, something is really wrong
        throw new InvalidArgumentException('Could not resolve humanize fallback strategy');
    }

    public static function apply(string $key, string $strategy): string
    {
        return static::resolve($strategy)->apply($key);
    }

    protected static function getStrategyClass(string $strategy): string
    {
        $strategies = Config::get('filament-smart-translate.fallback_strategies', []);

        // If it's a closure, we don't return it here
        if (isset($strategies[$strategy]) && is_callable($strategies[$strategy])) {
            return ''; // Will be handled in resolve method
        }

        return $strategies[$strategy] ?? static::getBuiltInStrategy($strategy);
    }

    protected static function getBuiltInStrategy(string $strategy): string
    {
        return match ($strategy) {
            'humanize' => HumanizeStrategy::class,
            'original' => OriginalStrategy::class,
            'title_case' => TitleCaseStrategy::class,
            default => throw new InvalidArgumentException("Unknown fallback strategy: {$strategy}"),
        };
    }
}
