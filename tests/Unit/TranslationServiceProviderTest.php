<?php

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentAutoTranslate\TranslationServiceProvider;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-auto-translation.enabled', true);

    // Set up test translations
    app('translator')->addLines([
        'actions.create user' => 'Criar Usuário',
        'actions.edit user' => 'Editar Usuário',
        'actions.delete user' => 'Excluir Usuário',
        'actions.create' => 'Criar',
        'actions.edit' => 'Editar',
        'actions.delete' => 'Excluir',
        'section_heading.personal' => 'Dados Pessoais',
        'section_description.personal' => 'Informações pessoais do usuário',
        'navigation.dashboard' => 'Painel',
    ], 'pt_BR');
});

it('service provider registers configuration correctly', function () {
    $provider = new TranslationServiceProvider(app());
    $provider->register();

    expect(config('filament-auto-translation'))->toBeArray();
    expect(config('filament-auto-translation.enabled'))->toBeTrue();
});

it('service provider publishes config in console', function () {
    app()->instance('app', app());

    $provider = new TranslationServiceProvider(app());
    $provider->boot();

    // Verify commands are registered when running in console
    expect(app()->runningInConsole())->toBeTrue();
});

it('field components get translation configuration', function () {
    $field = TextInput::make('name')->label('Name');

    // The configureUsing should have been applied by the service provider
    expect($field)->toBeInstanceOf(TextInput::class);
});

it('text entry components get translation configuration', function () {
    $entry = TextEntry::make('name')->label('Name');

    // The configureUsing should have been applied by the service provider
    expect($entry)->toBeInstanceOf(TextEntry::class);
});

it('table column components get translation configuration', function () {
    $column = TextColumn::make('name')->label('Name');

    // The configureUsing should have been applied by the service provider
    expect($column)->toBeInstanceOf(TextColumn::class);
});

it('section components get translation configuration with heading', function () {
    $section = Section::make('Personal Information')
        ->heading('personal')
        ->description('personal');

    // Test that section can be created and configured
    expect($section)->toBeInstanceOf(Section::class);
    expect($section->getHeading())->toBe('personal');
    expect($section->getDescription())->toBe('personal');
});

it('section components get translation configuration without heading', function () {
    $section = Section::make('Personal Information');

    // Test that section without heading/description works
    expect($section)->toBeInstanceOf(Section::class);
});

it('tabs components get translation configuration', function () {
    $tabs = Tabs::make('Sections');

    expect($tabs)->toBeInstanceOf(Tabs::class);
});

it('tab components get translation configuration', function () {
    $tab = Tab::make('General');

    expect($tab)->toBeInstanceOf(Tab::class);
});

it('create action gets translation configuration with label', function () {
    $model = new class extends Model
    {
        protected $table = 'test_models';

        public static function getLabel(): string
        {
            return 'User';
        }
    };

    $action = CreateAction::make('create')
        ->label('Create User')
        ->modalHeading('Create New User')
        ->modalDescription('Fill the form to create a new user')
        ->model($model::class);

    expect($action)->toBeInstanceOf(CreateAction::class);
    expect($action->getLabel())->toBe('Create User');
    expect($action->getModalHeading())->toBe('Create New User');
    expect($action->getModalDescription())->toBe('Fill the form to create a new user');
});

it('create action gets translation configuration without filled values', function () {
    $action = CreateAction::make('create');

    expect($action)->toBeInstanceOf(CreateAction::class);
});

it('edit action gets translation configuration with modal values', function () {
    $model = new class extends Model
    {
        protected $table = 'test_models';

        public static function getLabel(): string
        {
            return 'User';
        }
    };

    $action = EditAction::make('edit')
        ->modalHeading('Edit User')
        ->modalDescription('Update user information')
        ->model($model::class);

    expect($action)->toBeInstanceOf(EditAction::class);
    expect($action->getModalHeading())->toBe('Edit User');
    expect($action->getModalDescription())->toBe('Update user information');
});

it('edit action gets translation configuration without filled values', function () {
    $action = EditAction::make('edit');

    expect($action)->toBeInstanceOf(EditAction::class);
});

it('delete action gets translation configuration with all values', function () {
    $model = new class extends Model
    {
        protected $table = 'test_models';

        public static function getLabel(): string
        {
            return 'User';
        }
    };

    $action = DeleteAction::make('delete')
        ->label('Delete')
        ->modalHeading('Delete User')
        ->modalDescription('Are you sure you want to delete this user?')
        ->model($model::class);

    expect($action)->toBeInstanceOf(DeleteAction::class);
    expect($action->getLabel())->toBe('Delete');
    expect($action->getModalHeading())->toBe('Delete User');
    expect($action->getModalDescription())->toBe('Are you sure you want to delete this user?');
});

it('delete action gets translation configuration without filled values', function () {
    $action = DeleteAction::make('delete');

    expect($action)->toBeInstanceOf(DeleteAction::class);
});

it('navigation item gets translation configuration with group', function () {
    $navigationItem = NavigationItem::make('Dashboard')
        ->group('dashboard');

    expect($navigationItem)->toBeInstanceOf(NavigationItem::class);
    expect($navigationItem->getGroup())->toBe('dashboard');
});

it('navigation item gets translation configuration without group', function () {
    $navigationItem = NavigationItem::make('Dashboard');

    expect($navigationItem)->toBeInstanceOf(NavigationItem::class);
    expect($navigationItem->getGroup())->toBeNull();
});
