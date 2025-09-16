<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentSmartTranslate;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyManager;
use Throwable;

final class TranslationHelper
{
    private const DEFAULT_STRATEGY = 'original';

    private const DEFAULT_COMPONENT = 'default';

    /**
     * @param  array<string, mixed>  $replace
     */
    public static function translateWithFallback(
        string $key,
        string $component = self::DEFAULT_COMPONENT,
        array $replace = [],
        ?string $locale = null
    ): string {
        if (empty($key)) {
            return '';
        }

        try {
            // Check if translation system is globally enabled
            if (! Config::get('filament-smart-translate.enabled', true)) {
                return self::applyFallbackSafely($key, self::DEFAULT_STRATEGY);
            }

            // Get component configuration safely
            $componentEnabled = Config::get("filament-smart-translate.components.{$component}.enabled", true);
            $fallbackStrategy = Config::get("filament-smart-translate.components.{$component}.fallback_strategy", self::DEFAULT_STRATEGY);
            // Fix: Correct logic - if component is disabled, use original strategy
            if (! $componentEnabled) {
                self::logMissingTranslation($key, $component, self::DEFAULT_STRATEGY);

                return self::applyFallbackSafely($key, self::DEFAULT_STRATEGY);
            }

            // Component is enabled, try to find translation first
            $translationKey = "{$component}.{$key}";
            $translation = __($translationKey, $replace, $locale);

            // If translation found (not the same as translation key), return it
            if ($translation !== $translationKey) {
                return $translation;
            }

            // No translation found, use fallback strategy
            self::logMissingTranslation($key, $component, $fallbackStrategy);

            return self::applyFallbackSafely($key, $fallbackStrategy);
        } catch (Throwable $e) {
            // Log the error but don't let it break the application
            self::logError($e, $key, $component);

            return self::extractKeyLabel($key);
        }
    }

    private static function applyFallbackSafely(string $key, string $strategy): string
    {
        try {
            return FallbackStrategyManager::apply($key, $strategy);
        } catch (Throwable $e) {
            self::logError($e, $key, "strategy:{$strategy}");

            return self::extractKeyLabel($key);
        }
    }

    private static function extractKeyLabel(string $key): string
    {
        return (string) str($key)
            ->afterLast('.')
            ->replace(['-', '_'], ' ')
            ->ucfirst();
    }

    private static function logMissingTranslation(string $key, string $component, string $fallbackStrategy): void
    {
        if (! Config::get('filament-smart-translate.debug.log_missing_translations', false)) {
            return;
        }

        try {
            Log::info('Filament Smart Translation: Missing translation', [
                'key' => $key,
                'component' => $component,
                'fallback_strategy' => $fallbackStrategy,
                'locale' => app()->getLocale(),
            ]);
        } catch (Throwable) {
            // Silently ignore logging errors
        }
    }

    private static function logError(Throwable $exception, string $key, string $context): void
    {
        try {
            Log::error('Filament Smart Translation: Error occurred', [
                'exception' => $exception->getMessage(),
                'key' => $key,
                'context' => $context,
                'trace' => $exception->getTraceAsString(),
            ]);
        } catch (Throwable) {
            // Silently ignore logging errors
        }
    }
}
