<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Rodrigofs\FilamentSmartTranslate\TranslationServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Rodrigofs\\FilamentSmartTranslate\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        $this->setupTestTranslations();

        // Initialize Livewire for testing and start session to avoid ViewErrorBag issues
        \Livewire\Livewire::withoutLazyLoading();
        $this->startSession();
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Illuminate\Session\SessionServiceProvider::class,
            \Illuminate\View\ViewServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            \Filament\Actions\ActionsServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \Filament\Tables\TablesServiceProvider::class,
            \Filament\Notifications\NotificationsServiceProvider::class,
            \Filament\Infolists\InfolistsServiceProvider::class,
            \Filament\Schemas\SchemasServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            TranslationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');

        // Configure session for Livewire
        $app['config']->set('session.driver', 'array');
        $app['config']->set('session.lifetime', 120);
        $app['config']->set('session.expire_on_close', false);
        $app['config']->set('session.encrypt', false);
        $app['config']->set('session.files', storage_path('framework/sessions'));
        $app['config']->set('session.cookie', 'laravel_session');

        // Configure view for proper error bag handling
        $app['config']->set('view.paths', [resource_path('views')]);
        $app['config']->set('view.compiled', storage_path('framework/views'));

        // Set up test configuration
        $app['config']->set('filament-smart-translate', [
            'enabled' => true,
            'components' => [
                'resource_labels' => [
                    'enabled' => true,
                    'fallback_strategy' => 'original',
                    'cache_translations' => false,
                ],
                'navigation' => [
                    'enabled' => true,
                    'fallback_strategy' => 'original',
                    'cache_translations' => false,
                ],
                'actions' => [
                    'enabled' => true,
                    'fallback_strategy' => 'original',
                    'cache_translations' => false,
                ],
                'clusters' => [
                    'enabled' => true,
                    'fallback_strategy' => 'original',
                    'cache_translations' => false,
                ],
            ],
            'debug' => [
                'log_missing_translations' => false,
                'log_fallback_usage' => false,
            ],
            'fallback_strategies' => [],
        ]);

        $app['config']->set('app.locale', 'pt_BR');
    }

    protected function setupTestTranslations(): void
    {
        $this->app->make('translator')->addLines([
            'resource_labels.user' => 'Usuário',
            'resource_labels.users' => 'Usuários',
            'navigation_groups.admin' => 'Administração',
            'navigation.admin' => 'Administração',
            'actions.create' => 'Criar',
            'actions.edit' => 'Editar',
            'entries.email_address' => 'Endereço de Email',
            'entries.phone_number' => 'Número de Telefone',
            'columns.created_at' => 'Data de Criação',
            'columns.updated_at' => 'Data de Atualização',
            'clusters.settings' => 'Configurações',
        ], 'pt_BR');
    }

    protected function defineDatabaseMigrations(): void
    {
        // Define any required migrations for testing
    }
}
