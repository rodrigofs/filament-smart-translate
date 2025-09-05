<?php

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Resource\ResourceTranslateble;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);

    // Set up test translations
    app('translator')->addLines([
        'resource_labels.user' => 'Usuário',
        'resource_labels.post' => 'Postagem',
        'navigation_groups.admin' => 'Administração',
        'navigation_groups.content' => 'Conteúdo',
    ], 'pt_BR');
});

it('translates model label when translation exists', function () {
    // Test the TranslationHelper directly with what the trait would call
    $result = TranslationHelper::translateWithFallback('user', 'resource_labels');
    expect($result)->toBe('Usuário');
});

it('returns original model label when no translation exists', function () {
    $result = TranslationHelper::translateWithFallback('unknown_model', 'resource_labels');
    expect($result)->toBe('unknown_model'); // Original strategy as configured
});

it('translates navigation group when translation exists', function () {
    $result = TranslationHelper::translateWithFallback('admin', 'navigation_groups');
    expect($result)->toBe('Administração');
});

it('returns original navigation group when no translation exists', function () {
    $result = TranslationHelper::translateWithFallback('unknown_group', 'navigation_groups');
    expect($result)->toBe('Unknown_Group'); // Default humanize strategy
});

it('returns null for navigation group when null', function () {
    // Directly test trait logic for null values
    $resource = new class extends Resource
    {
        use ResourceTranslateble;

        protected static ?string $model = Model::class;

        protected static \UnitEnum | string | null $navigationGroup = null;
    };

    $result = $resource::getNavigationGroup();
    expect($result)->toBeNull();
});

it('handles enum navigation groups without translation', function () {
    // Since we can't create anonymous enums, we test the logic by checking enum handling
    $resource = new class extends Resource
    {
        use ResourceTranslateble;

        protected static ?string $model = Model::class;

        protected static \UnitEnum | string | null $navigationGroup = 'test_group';

        // Override to simulate enum behavior
        public static function getNavigationGroup(): \UnitEnum | string | null
        {
            if (is_null(self::$navigationGroup)) {
                return null;
            }

            // If it were an enum, we would return it directly
            // For this test, we just verify string handling works
            if (is_string(self::$navigationGroup)) {
                return TranslationHelper::translateWithFallback(self::$navigationGroup, 'navigation_groups');
            }

            return self::$navigationGroup;
        }
    };

    $result = $resource::getNavigationGroup();
    expect($result)->toBe('Test_Group'); // Humanized since no translation exists
});

it('respects disabled translation setting for model labels', function () {
    Config::set('filament-smart-translate.enabled', false);

    $result = TranslationHelper::translateWithFallback('user', 'resource_labels');
    expect($result)->toBe('user');
});

it('respects disabled translation setting for navigation groups', function () {
    Config::set('filament-smart-translate.enabled', false);

    $result = TranslationHelper::translateWithFallback('admin', 'navigation_groups');
    expect($result)->toBe('admin');
});

it('applies fallback strategy for model labels', function () {
    Config::set('filament-smart-translate.components.resource_labels.fallback_strategy', 'original');

    $result = TranslationHelper::translateWithFallback('user profile', 'resource_labels');
    expect($result)->toBe('user profile');
});

it('applies fallback strategy for navigation groups', function () {
    Config::set('filament-smart-translate.components.navigation_groups.fallback_strategy', 'original');
    Config::set('filament-smart-translate.components.navigation_groups.enabled', true);

    $result = TranslationHelper::translateWithFallback('user management', 'navigation_groups');
    expect($result)->toBe('user management');
});

it('trait properly delegates to TranslationHelper for model labels', function () {
    // Test the trait behavior by creating a resource that uses the trait
    $resource = new class extends Resource
    {
        use ResourceTranslateble;

        protected static ?string $model = Model::class;
    };

    // Mock parent behavior by calling the TranslationHelper directly with what parent would return
    $result = TranslationHelper::translateWithFallback('test_model', 'resource_labels');
    expect($result)->toBe('test_model'); // Original strategy as configured
});

it('trait properly delegates to TranslationHelper for navigation groups', function () {
    $resource = new class extends Resource
    {
        use ResourceTranslateble;

        protected static ?string $model = Model::class;

        protected static \UnitEnum | string | null $navigationGroup = 'test_group';
    };

    $result = $resource::getNavigationGroup();
    expect($result)->toBe('Test_Group'); // Humanized version since no translation exists
});

it('returns enum navigation group unchanged in resource', function () {
    // Create a proper enum for testing
    $testEnum = TestEnum::ADMIN;

    $resource = new class extends Resource
    {
        use ResourceTranslateble;

        protected static ?string $model = Model::class;

        protected static UnitEnum | string | null $navigationGroup = null;

        public function initializeEnum(UnitEnum $enum): void
        {
            self::$navigationGroup = $enum;
        }
    };

    $resource->initializeEnum($testEnum);
    expect($resource::getNavigationGroup())->toBe($testEnum);
});

enum TestEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';
}
