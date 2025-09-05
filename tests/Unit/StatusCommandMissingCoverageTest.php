<?php

use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('filament-smart-translate.enabled', true);
});

it('displays different coverage colors based on percentage', function () {
    // Test green color (80% or higher)
    Config::set('filament-smart-translate.components', [
        'resource_labels' => ['enabled' => true],
        'navigation' => ['enabled' => true],
        'actions' => ['enabled' => true],
        'clusters' => ['enabled' => true],
        'pages' => ['enabled' => false],
    ]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutput('    ▓ Active components: 4/5 (80%)');
});

it('displays coverage with default fallback strategy', function () {
    // Test default fallback handling
    Config::set('filament-smart-translate.components', [
        'resource_labels' => ['enabled' => true],
        'navigation' => ['enabled' => true, 'fallback_strategy' => 'unknown_fallback'],
        'actions' => ['enabled' => true, 'fallback_strategy' => 'custom_strategy'],
        'clusters' => ['enabled' => true],
        'pages' => ['enabled' => true],
    ]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutputToContain('▓ Active components: 5/5 (100%)')
        ->expectsOutputToContain('(unknown_fallback)')
        ->expectsOutputToContain('(custom_strategy)');
});

it('displays header correctly', function () {
    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutputToContain('Filament Smart Translation - Status Report');
});

it('displays all component sections', function () {
    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutputToContain('📦 Package Status:')
        ->expectsOutputToContain('🎯 Trait Usage:')
        ->expectsOutputToContain('🔧 Component Coverage:')
        ->expectsOutputToContain('📊 Coverage Summary:');
});

it('handles yellow coverage percentage range', function () {
    // Test yellow color (60-79%)
    Config::set('filament-smart-translate.components', [
        'resource_labels' => ['enabled' => true],
        'navigation' => ['enabled' => false],
        'actions' => ['enabled' => true],
        'clusters' => ['enabled' => true],
        'pages' => ['enabled' => false],
    ]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutput('    ▓ Active components: 3/5 (60%)');
});

it('shows info about traits when neither used nor candidates exist', function () {
    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutputToContain('💡 Info: Traits are optional and provide additional control over:')
        ->expectsOutputToContain('• ResourceTranslateble - Model labels in resources')
        ->expectsOutputToContain('• PageTranslateble - Navigation groups in pages')
        ->expectsOutputToContain('• ClusterTranslateble - Cluster breadcrumbs');
});

it('displays component status with different fallback strategies colors', function () {
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'humanize');
    Config::set('filament-smart-translate.components.navigation.fallback_strategy', 'title_case');
    Config::set('filament-smart-translate.components.actions.fallback_strategy', 'original');
    Config::set('filament-smart-translate.components.clusters.fallback_strategy', 'custom');

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutputToContain('(humanize)')
        ->expectsOutputToContain('(title_case)')
        ->expectsOutputToContain('(original)')
        ->expectsOutputToContain('(custom)');
});

it('returns success exit code', function () {
    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);
});

it('handles navigation component configuration', function () {
    Config::set('filament-smart-translate.components.navigation_groups.enabled', true);
    Config::set('filament-smart-translate.components.navigation_groups.fallback_strategy', 'title_case');

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutputToContain('Navigation');
});

it('displays zero traits when none are found', function () {
    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutputToContain('▓ Implemented traits: 0 files (optional)');
});
