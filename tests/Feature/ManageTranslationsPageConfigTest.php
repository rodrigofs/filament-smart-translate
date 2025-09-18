<?php

use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Pages\TranslationManagerPage;
use Rodrigofs\FilamentSmartTranslate\TranslationPlugin;

beforeEach(function () {
    // Reset config to defaults
    Config::set('filament-smart-translate.translation_page', [
        'enabled' => true,
        'dev_only' => false,
        'navigation' => [
            'group' => 'System',
            'label' => 'Translations',
            'icon' => 'heroicon-o-language',
            'sort' => 1000,
        ],
        'page' => [
            'title' => 'Manage Translations',
            'slug' => 'translations',
        ],
        'features' => [
            'backup' => true,
            'export' => true,
            'locale_selector' => true,
            'statistics' => true,
            'bulk_operations' => true,
        ],
        'middleware' => [],
        'authorize' => null,
    ]);
});

it('respects page enabled/disabled configuration', function () {
    // Test enabled (default)
    expect(TranslationManagerPage::shouldRegisterNavigation())->toBeTrue();
    expect(TranslationManagerPage::canAccess())->toBeTrue();

    // Test disabled
    Config::set('filament-smart-translate.translation_page.enabled', false);
    expect(TranslationManagerPage::shouldRegisterNavigation())->toBeFalse();
    expect(TranslationManagerPage::canAccess())->toBeFalse();
});

it('respects dev_only configuration', function () {
    Config::set('filament-smart-translate.translation_page.dev_only', true);

    // Mock different environments
    app()->instance('env', 'production');
    expect(TranslationManagerPage::shouldRegisterNavigation())->toBeFalse();
    expect(TranslationManagerPage::canAccess())->toBeFalse();

    // Test development environment
    app()->instance('env', 'local');
    expect(TranslationManagerPage::shouldRegisterNavigation())->toBeTrue();
    expect(TranslationManagerPage::canAccess())->toBeTrue();
});

it('uses configured navigation settings', function () {
    Config::set('filament-smart-translate.translation_page.navigation.group', 'Custom Group');
    Config::set('filament-smart-translate.translation_page.navigation.label', 'Custom Translations');
    Config::set('filament-smart-translate.translation_page.navigation.icon', 'heroicon-o-globe');
    Config::set('filament-smart-translate.translation_page.navigation.sort', 500);

    expect(TranslationManagerPage::getNavigationGroup())->toBe('Custom Group');
    expect(TranslationManagerPage::getNavigationLabel())->toBe('Custom Translations');
    expect(TranslationManagerPage::getNavigationIcon())->toBe('heroicon-o-globe');
    expect(TranslationManagerPage::getNavigationSort())->toBe(500);
});

it('uses configured page settings', function () {
    Config::set('filament-smart-translate.translation_page.page.title', 'Custom Title');
    Config::set('filament-smart-translate.translation_page.page.slug', 'custom-translations');

    $page = new TranslationManagerPage();
    expect($page->getTitle())->toBe('Custom Title');
    expect(TranslationManagerPage::getSlug())->toBe('custom-translations');
});

it('respects feature toggles in configuration', function () {
    // Test that configuration values are read correctly

    // All features enabled by default
    expect(Config::get('filament-smart-translate.translation_page.features.locale_selector'))->toBeTrue();
    expect(Config::get('filament-smart-translate.translation_page.features.export'))->toBeTrue();
    expect(Config::get('filament-smart-translate.translation_page.features.backup'))->toBeTrue();
    expect(Config::get('filament-smart-translate.translation_page.features.statistics'))->toBeTrue();
    expect(Config::get('filament-smart-translate.translation_page.features.bulk_operations'))->toBeTrue();

    // Disable features
    Config::set('filament-smart-translate.translation_page.features.locale_selector', false);
    Config::set('filament-smart-translate.translation_page.features.export', false);
    Config::set('filament-smart-translate.translation_page.features.backup', false);
    Config::set('filament-smart-translate.translation_page.features.statistics', false);
    Config::set('filament-smart-translate.translation_page.features.bulk_operations', false);

    expect(Config::get('filament-smart-translate.translation_page.features.locale_selector'))->toBeFalse();
    expect(Config::get('filament-smart-translate.translation_page.features.export'))->toBeFalse();
    expect(Config::get('filament-smart-translate.translation_page.features.backup'))->toBeFalse();
    expect(Config::get('filament-smart-translate.translation_page.features.statistics'))->toBeFalse();
    expect(Config::get('filament-smart-translate.translation_page.features.bulk_operations'))->toBeFalse();
});

it('plugin respects configuration for page registration', function () {
    $plugin = new TranslationPlugin();
    $panel = \Filament\Panel::make();

    // Page enabled by default - should register
    Config::set('filament-smart-translate.translation_page.enabled', true);
    $plugin->register($panel);
    expect($panel->getPages())->toContain(TranslationManagerPage::class);

    // Reset panel
    $panel = \Filament\Panel::make();

    // Page disabled - should not register
    Config::set('filament-smart-translate.translation_page.enabled', false);
    $plugin->register($panel);
    expect($panel->getPages())->not->toContain(TranslationManagerPage::class);
});

it('plugin respects dev_only configuration', function () {
    $plugin = new TranslationPlugin();

    Config::set('filament-smart-translate.translation_page.dev_only', true);

    // Production environment - should not register
    app()->instance('env', 'production');
    $panel = \Filament\Panel::make();
    $plugin->register($panel);
    expect($panel->getPages())->not->toContain(TranslationManagerPage::class);

    // Development environment - should register
    app()->instance('env', 'local');
    $panel = \Filament\Panel::make();
    $plugin->register($panel);
    expect($panel->getPages())->toContain(TranslationManagerPage::class);
});

it('handles custom authorization callback', function () {
    // Test with callback that returns false
    Config::set('filament-smart-translate.translation_page.authorize', fn () => false);
    expect(TranslationManagerPage::canAccess())->toBeFalse();

    // Test with callback that returns true
    Config::set('filament-smart-translate.translation_page.authorize', fn () => true);
    expect(TranslationManagerPage::canAccess())->toBeTrue();

    // Test with null (no authorization)
    Config::set('filament-smart-translate.translation_page.authorize', null);
    expect(TranslationManagerPage::canAccess())->toBeTrue();
});
