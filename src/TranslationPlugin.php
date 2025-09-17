<?php

namespace Rodrigofs\FilamentSmartTranslate;

use Filament\Contracts\Plugin;
use Filament\Panel;

class TranslationPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-smart-translate';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            \Rodrigofs\FilamentSmartTranslate\Pages\ManageTranslationsPage::class,
        ]);
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
