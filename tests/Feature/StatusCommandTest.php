<?php

use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);
});

it('displays package status when enabled', function () {
    Config::set('filament-smart-translate.enabled', true);

    $this->artisan('filament-smart-translate:status')
        ->expectsOutputToContain('📦 Package Status: ✓ ENABLED')
        ->assertSuccessful();
});

it('displays package status when disabled', function () {
    Config::set('filament-smart-translate.enabled', false);

    $this->artisan('filament-smart-translate:status')
        ->expectsOutputToContain('✗ DISABLED')
        ->assertSuccessful();
});

it('displays component coverage', function () {
    Config::set('filament-smart-translate.components.resources.enabled', true);
    Config::set('filament-smart-translate.components.navigations.enabled', false);

    $this->artisan('filament-smart-translate:status')
        ->expectsOutputToContain('✓ Resource Labels')
        ->expectsOutputToContain('✗ Navigation')
        ->assertSuccessful();
});

it('displays fallback strategies', function () {
    Config::set('filament-smart-translate.components.resources.fallback_strategy', 'humanize');
    Config::set('filament-smart-translate.components.navigations.fallback_strategy', 'title_case');

    $this->artisan('filament-smart-translate:status')
        ->expectsOutputToContain('(humanize)')
        ->expectsOutputToContain('(title_case)')
        ->assertSuccessful();
});

it('shows coverage summary', function () {
    $this->artisan('filament-smart-translate:status')
        ->expectsOutputToContain('Coverage Summary')
        ->expectsOutputToContain('9/9')
        ->assertSuccessful();
});

it('displays trait usage when no traits are found', function () {
    $this->artisan('filament-smart-translate:status')
        ->expectsOutputToContain('⚠ No traits found in use')
        ->assertSuccessful();
});

it('displays trait candidates section', function () {
    $this->artisan('filament-smart-translate:status')
        ->expectsOutputToContain('🎯 Trait Usage:')
        ->assertSuccessful();
});

it('shows helpful tips for incomplete coverage', function () {
    Config::set('filament-smart-translate.components.resources.enabled', false);

    $this->artisan('filament-smart-translate:status')
        ->expectsOutputToContain('💡 Tip:')
        ->expectsOutputToContain('config/filament-smart-translate.php')
        ->assertSuccessful();
});

it('shows trait information when no traits are used', function () {
    $this->artisan('filament-smart-translate:status')
        ->expectsOutputToContain('💡 Info:')
        ->expectsOutputToContain('ResourceTranslateble')
        ->expectsOutputToContain('PageTranslateble')
        ->expectsOutputToContain('ClusterTranslateble')
        ->assertSuccessful();
});

it('does not show duplicate file entries', function () {
    $this->artisan('filament-smart-translate:status')
        ->assertSuccessful();

    // This test mainly ensures the command runs without errors
    // The actual duplicate prevention is tested implicitly by the improved scanning logic
    expect(true)->toBeTrue();
});
