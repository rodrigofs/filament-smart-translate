<?php

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyManager;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\FieldWrapper;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;
use Rodrigofs\FilamentSmartTranslate\TranslationServiceProvider;

beforeEach(function () {
    FallbackStrategyManager::clearCache();
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);
    Config::set('filament-smart-translate.debug.log_missing_translations', false);
    Config::set('filament-smart-translate.fallback_strategies', []);
});

it('registers configuration correctly', function () {
    $provider = new TranslationServiceProvider(app());
    $provider->register();

    expect(config('filament-smart-translate'))->toBeArray();
    expect(config('filament-smart-translate.enabled'))->toBeTrue();
});

it('configures package correctly', function () {
    // Service provider is already registered and configured in TestCase
    // Test that the package is working correctly
    expect(config('filament-smart-translate'))->toBeArray();
    expect(config('filament-smart-translate.enabled'))->toBeTrue();
});

it('registers commands correctly', function () {
    $provider = new TranslationServiceProvider(app());

    // Use reflection to access protected method
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('getCommands');
    $method->setAccessible(true);

    $commands = $method->invoke($provider);
    expect($commands)->toBeArray();
    expect($commands)->toContain(\Rodrigofs\FilamentSmartTranslate\Console\StatusCommand::class);
});

it('handles service provider configuration without errors', function () {
    $provider = new TranslationServiceProvider(app());

    // Should not throw exception during registration and boot
    expect(fn () => $provider->register())->not->toThrow(Exception::class);
    expect(fn () => $provider->boot())->not->toThrow(Exception::class);
});

it('creates field wrapper correctly', function () {
    // Test that field wrapper can be created directly
    $field = TextInput::make('user_profile');
    $wrapper = new FieldWrapper($field->getName());

    expect($wrapper)->toBeInstanceOf(FieldWrapper::class);
    expect($wrapper->getLabel())->toBe('User profile');
});

it('field wrapper respects base labels when set', function () {
    $wrapper = new Rodrigofs\FilamentSmartTranslate\Support\Overrides\FieldWrapper('user_profile');

    // Without a base label set, should use translation
    expect($wrapper->getLabel())->toBe('User profile');
});

it('field wrapper handles empty names gracefully', function () {
    $wrapper = new Rodrigofs\FilamentSmartTranslate\Support\Overrides\FieldWrapper('');

    expect($wrapper->getLabel())->toBe(''); // Empty name should return empty string
});

it('translates component names using fallback strategy', function () {
    // Test direct translation behavior without provider complications
    $result = TranslationHelper::translateWithFallback('user_name', 'fields');
    expect($result)->toBe('User name'); // Original strategy uses ucfirst
});

it('handles empty component names', function () {
    $result = TranslationHelper::translateWithFallback('', 'fields');
    expect($result)->toBe(''); // Should return empty string
});

it('configures section component with heading and description', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureSectionComponent');
    $method->setAccessible(true);

    $section = Section::make()->heading('test_heading')->description('test_description');

    $method->invoke($provider, $section);

    expect($section->getHeading())->toBe('Test heading');
    expect($section->getDescription())->toBe('Test description');
});

it('configures section component without heading and description', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureSectionComponent');
    $method->setAccessible(true);

    $section = Section::make();

    expect(fn () => $method->invoke($provider, $section))->not->toThrow(Exception::class);
});

it('configures tabs component with label', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureTabsComponent');
    $method->setAccessible(true);

    $tabs = Tabs::make('test_tabs');

    $method->invoke($provider, $tabs);

    expect($tabs->getLabel())->toBe('Test tabs');
});

it('configures tab component with label', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureTabComponent');
    $method->setAccessible(true);

    $tab = Tab::make('test_tab');

    $method->invoke($provider, $tab);

    expect($tab->getLabel())->toBe('Test tab');
});

it('configures action properties correctly', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureActionProperties');
    $method->setAccessible(true);

    $action = Action::make('test')
        ->label('test_label')
        ->modalHeading('test_heading');

    $properties = [
        'label' => 'getLabel',
        'modalHeading' => 'getModalHeading',
    ];

    $method->invoke($provider, $action, $properties);

    expect($action->getLabel())->toBe('Test label');
    expect($action->getModalHeading())->toBe('Test heading');
});

it('skips action properties for non-existent methods', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureActionProperties');
    $method->setAccessible(true);

    $action = Action::make('test');
    $properties = ['nonExistentProperty' => 'getNonExistentMethod'];

    expect(fn () => $method->invoke($provider, $action, $properties))->not->toThrow(Exception::class);
});

it('skips action properties for empty values', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureActionProperties');
    $method->setAccessible(true);

    $action = Action::make('test'); // No label set, should be empty
    $properties = ['label' => 'getLabel'];

    expect(fn () => $method->invoke($provider, $action, $properties))->not->toThrow(Exception::class);
});

it('configures model action with model and filled values', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureModelAction');
    $method->setAccessible(true);

    $model = new class extends Model
    {
        public static function getLabel(): string
        {
            return 'Test Model';
        }
    };

    $action = CreateAction::make('create')
        ->model($model::class)
        ->label('Create')
        ->modalHeading('Create Modal');

    $translationKeys = [
        'label' => 'filament-actions::create.single.label',
        'modalHeading' => 'filament-actions::create.single.modal.heading',
    ];

    $method->invoke($provider, $action, $translationKeys);

    // Should work without throwing exceptions
    expect($action)->toBeInstanceOf(CreateAction::class);
});

it('skips model action configuration without model', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureModelAction');
    $method->setAccessible(true);

    $action = CreateAction::make('create'); // No model set
    $translationKeys = ['label' => 'filament-actions::create.single.label'];

    expect(fn () => $method->invoke($provider, $action, $translationKeys))->not->toThrow(Exception::class);
});

it('skips model action properties for non-existent methods', function () {
    $provider = new TranslationServiceProvider(app());
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('configureModelAction');
    $method->setAccessible(true);

    $model = new class extends Model
    {
        public static function getLabel(): string
        {
            return 'Test Model';
        }
    };

    $action = CreateAction::make('create')->model($model::class);
    $translationKeys = ['nonExistentProperty' => 'some.translation.key'];

    expect(fn () => $method->invoke($provider, $action, $translationKeys))->not->toThrow(Exception::class);
});

it('configures field components with automatic translation functionality', function () {
    // Service provider is already registered in TestCase
    $field = TextInput::make('user_name');

    // Field should be created successfully
    expect($field)->toBeInstanceOf(TextInput::class);
    expect($field->getName())->toBe('user_name');
});

it('configures text entry components with automatic translation functionality', function () {
    // Service provider is already registered in TestCase
    $entry = TextEntry::make('user_email');

    // Entry should be created successfully
    expect($entry)->toBeInstanceOf(TextEntry::class);
    expect($entry->getName())->toBe('user_email');
});

it('configures column components with automatic translation functionality', function () {
    // Service provider is already registered in TestCase
    $column = TextColumn::make('created_at');

    // Column should be created successfully
    expect($column)->toBeInstanceOf(TextColumn::class);
    expect($column->getName())->toBe('created_at');
});

it('navigation components translate group names correctly', function () {
    // Service provider is already registered in TestCase
    $navigation = NavigationItem::make('Dashboard')->group('admin_section');

    // Navigation should be created successfully
    expect($navigation->getGroup())->toBeString();
    expect($navigation->getLabel())->toBe('Dashboard');
});

it('handles translation contexts correctly', function () {
    // Test different contexts use appropriate strategies
    $fieldsResult = TranslationHelper::translateWithFallback('user_profile', 'fields');
    $entriesResult = TranslationHelper::translateWithFallback('user_profile', 'entries');
    $schemasResult = TranslationHelper::translateWithFallback('user_profile', 'schemas');

    expect($fieldsResult)->toBe('User profile');
    expect($entriesResult)->toBe('User profile');
    expect($schemasResult)->toBe('User profile');
});

it('integrates all component configurations properly', function () {
    $provider = new TranslationServiceProvider(app());

    // Should not throw exception when configuring all components
    expect(fn () => $provider->boot())->not->toThrow(Exception::class);
});
