<?php

use Filament\Forms\Components\TextInput;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;
use Rodrigofs\FilamentSmartTranslate\TranslationServiceProvider;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);

    // Set up test translations
    app('translator')->addLines([
        'resource_labels.user' => 'Usuário',
        'navigation.admin' => 'Administração',
        'actions.create' => 'Criar',
    ], 'pt_BR');
});

it('registers the service provider', function () {
    $providers = app()->getLoadedProviders();

    expect($providers)->toHaveKey(TranslationServiceProvider::class);
});

it('creates filament components successfully', function () {
    // Test that components can be instantiated (they get auto-configured by service provider)
    $field = TextInput::make('name');
    $column = TextColumn::make('email');
    $navItem = NavigationItem::make('Dashboard');

    expect($field)->toBeInstanceOf(TextInput::class);
    expect($column)->toBeInstanceOf(TextColumn::class);
    expect($navItem)->toBeInstanceOf(NavigationItem::class);
});

it('translates different component types', function () {
    $userLabel = TranslationHelper::translateWithFallback('user', 'resource_labels');
    $adminNav = TranslationHelper::translateWithFallback('admin', 'navigation');
    $createAction = TranslationHelper::translateWithFallback('create', 'actions');

    expect($userLabel)->toBe('Usuário');
    expect($adminNav)->toBe('Administração');
    expect($createAction)->toBe('Criar');
});

it('loads configuration correctly', function () {
    expect(Config::get('filament-smart-translate.enabled'))->toBeTrue();
    expect(Config::get('filament-smart-translate.components'))->toBeArray();
});

it('handles navigation group translation', function () {
    $result = TranslationHelper::translateWithFallback('admin', 'navigation');

    expect($result)->toBe('Administração');
});

it('supports multiple fallback strategies', function () {
    // Test original strategy
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'original');
    $result1 = TranslationHelper::translateWithFallback('unknown_key', 'resource_labels');

    // Test humanize strategy
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'humanize');
    $result2 = TranslationHelper::translateWithFallback('user_profile', 'resource_labels');

    // Test title_case strategy
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'title_case');
    $result3 = TranslationHelper::translateWithFallback('user profile', 'resource_labels');

    expect($result1)->toBe('Unknown key');  // Original strategy: afterLast('.') + replace + ucfirst
    expect($result2)->toBe('User Profile');  // Humanize strategy: afterLast('.') + replace + title
    expect($result3)->toBe('user profile');  // Title_case strategy (alias for lower_case): afterLast('.') + replace + lower
});
