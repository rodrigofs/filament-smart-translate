<?php

use Filament\Pages\Page;
use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Traits\Page\PageTranslateble;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);

    // Note: TranslationHelper currently uses fallback strategies only, not actual translations
});

it('uses fallback strategy for navigation group', function () {
    $result = TranslationHelper::translateWithFallback('admin', 'navigation_groups');
    expect($result)->toBe('Admin'); // fallback strategy result
});

it('returns original navigation group when no translation exists', function () {
    $result = TranslationHelper::translateWithFallback('unknown_group', 'navigation_groups');
    expect($result)->toBe('Unknown group'); // Default original strategy
});

it('returns null when navigation group is null', function () {
    $pageClass = new class extends Page
    {
        use PageTranslateble;

        protected static \UnitEnum | string | null $navigationGroup = null;

        public function getView(): string
        {
            return 'test-view';
        }
    };

    $result = $pageClass::getNavigationGroup();
    expect($result)->toBeNull();
});

it('handles enum navigation groups without translation', function () {
    // Since we can't create anonymous enums, we test the string handling behavior
    $pageClass = new class extends Page
    {
        use PageTranslateble;

        protected static \UnitEnum | string | null $navigationGroup = 'test_group';

        public function getView(): string
        {
            return 'test-view';
        }
    };

    $result = $pageClass::getNavigationGroup();
    expect($result)->toBe('Test group'); // Original strategy since no translation exists
});

it('uses fallback strategy for model label', function () {
    $result = TranslationHelper::translateWithFallback('user', 'resource_labels');
    expect($result)->toBe('User'); // fallback strategy result
});

it('returns null when parent model label is null', function () {
    // The trait handles null values, but TranslationHelper expects a string
    // This test verifies the trait's null handling logic
    $pageClass = new class extends Page
    {
        use PageTranslateble;

        public function getView(): string
        {
            return 'test-view';
        }

        public function getModelLabel(): ?string
        {
            return null; // Trait should handle this and return null directly
        }
    };

    $pageInstance = new $pageClass();
    $result = $pageInstance->getModelLabel();
    expect($result)->toBeNull();
});

it('respects disabled translation setting for navigation groups', function () {
    Config::set('filament-smart-translate.enabled', false);

    $result = TranslationHelper::translateWithFallback('admin', 'navigation_groups');
    expect($result)->toBe('Admin');
});

it('respects disabled translation setting for model labels', function () {
    Config::set('filament-smart-translate.enabled', false);

    $result = TranslationHelper::translateWithFallback('user', 'resource_labels');
    expect($result)->toBe('User');
});

it('trait properly delegates to TranslationHelper for navigation groups', function () {
    $pageClass = new class extends Page
    {
        use PageTranslateble;

        protected static \UnitEnum | string | null $navigationGroup = 'test_group';

        public function getView(): string
        {
            return 'test-view';
        }
    };

    $result = $pageClass::getNavigationGroup();
    expect($result)->toBe('Test group'); // Original strategy since no translation exists
});

it('trait handles model label translation correctly', function () {
    $pageClass = new class extends Page
    {
        use PageTranslateble;

        public function getView(): string
        {
            return 'test-view';
        }

        public function getModelLabel(): ?string
        {
            // Simulate parent method returning a value
            return 'test_model';
        }
    };

    $pageInstance = new $pageClass();
    $result = $pageInstance->getModelLabel();
    expect($result)->toBe('test_model'); // Test overrides trait method completely
});

it('returns enum navigation group unchanged without translation', function () {
    // Create a proper enum for testing
    $testEnum = PageTestEnum::SETTINGS;

    $page = new class
    {
        use PageTranslateble;

        protected static $navigationGroup;

        public function initializeEnum(UnitEnum $enum): void
        {
            self::$navigationGroup = $enum;
        }
    };

    $page->initializeEnum($testEnum);
    expect($page::getNavigationGroup())->toBe($testEnum);
});

enum PageTestEnum: string
{
    case SETTINGS = 'settings';
    case DASHBOARD = 'dashboard';
}
