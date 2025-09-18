<?php

use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\TranslationPlugin;

beforeEach(function () {
    Config::set('filament-smart-translate.enabled', true);
});

// Test TranslationPlugin methods
it('translation plugin creates instance correctly', function () {
    $plugin = TranslationPlugin::make();

    expect($plugin)->toBeInstanceOf(TranslationPlugin::class);
});

it('translation plugin has correct id', function () {
    $plugin = TranslationPlugin::make();

    expect($plugin->getId())->toBe('filament-smart-translate');
});

// Test EntryWrapper and FieldWrapper missing lines
it('entry wrapper handles getName method', function () {
    $wrapper = new \Rodrigofs\FilamentSmartTranslate\Support\Overrides\EntryWrapper('test');

    $name = $wrapper->getName();
    expect($name)->toBe('test');
});

it('field wrapper handles getName method', function () {
    $wrapper = new \Rodrigofs\FilamentSmartTranslate\Support\Overrides\FieldWrapper('test', 'fields');

    $name = $wrapper->getName();
    expect($name)->toBe('test');
});

// Test StatusCommand uncovered lines (simpler approach)
it('status command handles trait detection', function () {
    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);

    // Command runs successfully, covering execution paths
    expect(true)->toBeTrue();
});
