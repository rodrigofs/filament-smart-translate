<?php

namespace Rodrigofs\FilamentSmartTranslate;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Config;

class TranslationPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-smart-translate';
    }

    public function register(Panel $panel): void
    {
        // Only register the page if it's enabled and meets all conditions
        if ($this->shouldRegisterPage()) {
            $panel->pages([
                \Rodrigofs\FilamentSmartTranslate\Pages\TranslationManagerPage::class,
            ]);
        }
    }

    private function shouldRegisterPage(): bool
    {
        // Check if the page is enabled
        if (! Config::get('filament-smart-translate.translation_page.enabled', true)) {
            return false;
        }

        // Check if it should only be shown in development
        if (Config::get('filament-smart-translate.translation_page.dev_only', false)) {
            return app()->environment('local', 'development', 'testing');
        }

        return true;
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
