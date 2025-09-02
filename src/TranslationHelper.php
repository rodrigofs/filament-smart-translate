<?php

namespace Rodrigofs\FilamentAutoTranslate;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TranslationHelper
{
    /**
     * @param  array<string ,mixed>  $replace
     */
    public static function translateWithFallback(
        string $key,
        string $component = 'default',
        array $replace = [],
        ?string $locale = null
    ): string {
        // Check if translation system is enabled
        if (! Config::get('filament-auto-translation.enabled', true)) {
            return $key;
        }

        // Check if specific component is enabled
        $componentConfig = Config::get("filament-auto-translation.components.{$component}", ['enabled' => true]);
        if ($componentConfig && ! ($componentConfig['enabled'] ?? true)) {
            return $key;
        }

        // Try Laravel translation with component prefix
        $translationKey = $component !== 'default' ? "{$component}.{$key}" : $key;

        $translated = __($translationKey, $replace, $locale);

        // If translation was found (not the same as key), return it
        if ($translated !== $translationKey) {
            return $translated;
        }

        // Also try without component prefix as fallback
        $directTranslated = __($key, $replace, $locale);
        if ($directTranslated !== $key) {
            return $directTranslated;
        }

        // Apply fallback strategy
        $fallbackStrategy = ($componentConfig['fallback_strategy'] ?? null) ?: 'humanize';
        $fallbackResult = static::applyFallbackStrategy($key, $fallbackStrategy);

        // Log missing translation if debug is enabled
        static::logMissingTranslation($key, $component, $fallbackStrategy);

        return $fallbackResult;
    }

    /**
     * Apply fallback strategy
     */
    protected static function applyFallbackStrategy(string $key, string $strategy): string
    {
        $strategies = Config::get('filament-auto-translation.fallback_strategies', []);

        if (isset($strategies[$strategy]) && is_callable($strategies[$strategy])) {
            return $strategies[$strategy]($key);
        }

        // Default fallback strategies
        return match ($strategy) {
            'humanize' => Str::title(Str::snake($key, ' ')),
            'title_case' => ucwords($key),
            'original' => $key,
            default => Str::title(Str::snake($key, ' ')),
        };
    }

    /**
     * Log missing translation for debugging
     */
    protected static function logMissingTranslation(string $key, string $component, string $fallbackStrategy): void
    {
        if (! Config::get('filament-auto-translation.debug.log_missing_translations', false)) {
            return;
        }

        Log::info('Filament Auto Translation: Missing translation', [
            'key' => $key,
            'component' => $component,
            'fallback_strategy' => $fallbackStrategy,
            'locale' => app()->getLocale(),
        ]);
    }
}
