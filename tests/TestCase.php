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
    }

    protected function getPackageProviders($app): array
    {
        return [
            TranslationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');

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
            'navigation.admin' => 'Administração',
            'actions.create' => 'Criar',
            'actions.edit' => 'Editar',
        ], 'pt_BR');
    }

    protected function defineDatabaseMigrations(): void
    {
        // Define any required migrations for testing
    }
}
