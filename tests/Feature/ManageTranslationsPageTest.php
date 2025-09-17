<?php

use Livewire\Livewire;
use Rodrigofs\FilamentSmartTranslate\Pages\ManageTranslationsPage;
use Rodrigofs\FilamentSmartTranslate\Services\TranslationService;

beforeEach(function () {
    $this->tempPath = sys_get_temp_dir() . '/translation_page_test_' . uniqid();
    mkdir($this->tempPath, 0755, true);

    // Create mock translation service
    $this->translationService = new TranslationService($this->tempPath, ['pt_BR', 'en']);

    // Bind service to container for dependency injection
    $this->app->instance(TranslationService::class, $this->translationService);

    // Set up test translations
    $this->testTranslations = [
        'resources.user' => 'Usuário',
        'actions.create' => 'Criar',
        'validation.required' => 'Este campo é obrigatório',
        'ui.button.save' => 'Salvar',
    ];

    foreach ($this->testTranslations as $key => $value) {
        $this->translationService->addTranslation('pt_BR', $key, $value);
    }
});

afterEach(function () {
    // Clean up test files
    if (is_dir($this->tempPath)) {
        array_map('unlink', glob("{$this->tempPath}/*"));
        if (is_dir("{$this->tempPath}/backups")) {
            array_map('unlink', glob("{$this->tempPath}/backups/*"));
            rmdir("{$this->tempPath}/backups");
        }
        rmdir($this->tempPath);
    }
});

it('can render the manage translations page', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Assert
    $component
        ->assertOk()
        ->assertSee('Gerenciar Traduções')
        ->assertSee('resources.user')
        ->assertSee('Usuário');
});

it('displays all translations in table', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Assert - Check that all test translations are displayed
    foreach ($this->testTranslations as $key => $value) {
        $component
            ->assertSee($key)
            ->assertSee($value);
    }
});

it('can add a new translation', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Add new translation
    $component
        ->callAction('add_translation', [
            'locale' => 'pt_BR',
            'key' => 'new.test.key',
            'value' => 'Novo valor de teste',
        ]);

    // Assert
    $component
        ->assertSuccessful()
        ->assertSee('new.test.key')
        ->assertSee('Novo valor de teste')
        ->assertNotified('Tradução adicionada');

    // Verify it was actually saved
    $translations = $this->translationService->loadTranslations('pt_BR');
    expect($translations->has('new.test.key'))->toBeTrue();
    expect($translations->get('new.test.key'))->toBe('Novo valor de teste');
});

it('prevents adding duplicate translation keys', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Try to add duplicate key
    $component
        ->callAction('add_translation', [
            'locale' => 'pt_BR',
            'key' => 'resources.user', // Already exists
            'value' => 'Duplicate value',
        ]);

    // Assert
    $component->assertNotified('Erro ao adicionar tradução');

    // Verify original value wasn't changed
    $translations = $this->translationService->loadTranslations('pt_BR');
    expect($translations->get('resources.user'))->toBe('Usuário'); // Original value
});

it('can edit an existing translation', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Find record and edit it
    $component
        ->callTableAction('edit', 'resources.user', [
            'value' => 'Usuário Editado',
        ]);

    // Assert
    $component
        ->assertSuccessful()
        ->assertSee('Usuário Editado')
        ->assertNotified('Tradução atualizada');

    // Verify it was actually updated
    $translations = $this->translationService->loadTranslations('pt_BR');
    expect($translations->get('resources.user'))->toBe('Usuário Editado');
});

it('can delete a translation', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Delete translation
    $component->callTableAction('delete', 'resources.user');

    // Assert
    $component
        ->assertSuccessful()
        ->assertDontSee('resources.user')
        ->assertNotified('Tradução excluída');

    // Verify it was actually deleted
    $translations = $this->translationService->loadTranslations('pt_BR');
    expect($translations->has('resources.user'))->toBeFalse();
});

it('can bulk delete translations', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Select multiple records and delete them
    $component->callTableBulkAction('delete', ['resources.user', 'actions.create']);

    // Assert
    $component
        ->assertSuccessful()
        ->assertDontSee('resources.user')
        ->assertDontSee('actions.create')
        ->assertNotified('Traduções excluídas');

    // Verify they were actually deleted
    $translations = $this->translationService->loadTranslations('pt_BR');
    expect($translations->has('resources.user'))->toBeFalse();
    expect($translations->has('actions.create'))->toBeFalse();
    expect($translations->has('validation.required'))->toBeTrue(); // Should still exist
});

it('can change locale', function () {
    // Setup - Add translations for another locale
    $this->translationService->addTranslation('en', 'resources.user', 'User');
    $this->translationService->addTranslation('en', 'actions.create', 'Create');

    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Change locale
    $component->callAction('change_locale', [
        'locale' => 'en',
    ]);

    // Assert
    $component
        ->assertSuccessful()
        ->assertSee('User')
        ->assertSee('Create')
        ->assertDontSee('Usuário') // Portuguese translation should not be visible
        ->assertNotified('Idioma alterado');
});

it('can refresh translations', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Manually add a translation to the file system (simulating external change)
    $this->translationService->addTranslation('pt_BR', 'external.key', 'External Value');

    // Refresh
    $component->callAction('refresh');

    // Assert
    $component
        ->assertSuccessful()
        ->assertSee('external.key')
        ->assertSee('External Value')
        ->assertNotified('Traduções recarregadas');
});

it('can export translations', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Call export action
    $response = $component->callAction('export');

    // Assert
    expect($response)->toBeInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class);

    // Check response headers
    $headers = $response->headers;
    expect($headers->get('content-disposition'))->toContain('translations-pt_BR.json');
    expect($headers->get('content-type'))->toContain('application/octet-stream');
});

it('can create backup', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Create backup
    $component->callAction('backup');

    // Assert
    $component
        ->assertSuccessful()
        ->assertNotified('Backup criado');

    // Verify backup file exists
    $backupDir = "{$this->tempPath}/backups";
    expect(is_dir($backupDir))->toBeTrue();

    $backupFiles = glob("{$backupDir}/pt_BR_*.json");
    expect($backupFiles)->not->toBeEmpty();

    // Verify backup content
    $backupContent = json_decode(file_get_contents($backupFiles[0]), true);
    expect($backupContent)->toHaveKey('resources.user');
    expect($backupContent['resources.user'])->toBe('Usuário');
});

it('can view statistics', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // View statistics
    $component->callAction('statistics');

    // Assert - The infolist modal should be displayed
    $component->assertSuccessful();

    // Check that statistics are properly loaded
    expect($component->get('statistics')['total'])->toBe(4);
    expect($component->get('statistics'))->toHaveKey('empty');
    expect($component->get('statistics'))->toHaveKey('long');
    expect($component->get('statistics'))->toHaveKey('average_length');
});

it('displays translation categories in table', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Assert - Check that categories are displayed as badges
    $component
        ->assertSee('resources') // Category for 'resources.user'
        ->assertSee('actions')   // Category for 'actions.create'
        ->assertSee('validation') // Category for 'validation.required'
        ->assertSee('ui');        // Category for 'ui.button.save'
});

it('shows translation lengths in table', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Assert - Check that lengths are calculated and displayed
    $component->assertSuccessful();

    // The lengths should be calculated correctly
    $translations = $component->get('translations');
    $userTranslation = $translations->firstWhere('key', 'resources.user');

    expect($userTranslation['length'])->toBe(7); // Length of "Usuário"
    expect($userTranslation['is_empty'])->toBeFalse();
    expect($userTranslation['is_long'])->toBeFalse();
});

it('handles empty locale gracefully', function () {
    // Setup - Create component with empty locale
    $component = Livewire::test(ManageTranslationsPage::class);

    // Change to non-existent locale
    $component->callAction('change_locale', [
        'locale' => 'fr', // Not in available locales, but should not crash
    ]);

    // Assert - Should handle gracefully
    $component->assertSuccessful();
});

it('validates required fields in add translation form', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Try to add translation with empty required fields
    $component
        ->callAction('add_translation', [
            'locale' => '',
            'key' => '',
            'value' => '',
        ]);

    // Assert - Should have validation errors
    $component->assertHasActionErrors(['locale', 'key', 'value']);
});

it('validates maximum length in add translation form', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Try to add translation with value too long
    $component
        ->callAction('add_translation', [
            'locale' => 'pt_BR',
            'key' => 'test.key',
            'value' => str_repeat('a', 1001), // Exceeds 1000 char limit
        ]);

    // Assert - Should have validation error
    $component->assertHasActionErrors(['value']);
});

it('can search translations in table', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Search for specific term
    $component->searchTable('resources');

    // Assert - Should only show matching translations
    $component
        ->assertCanSeeTableRecords([
            ['key' => 'resources.user', 'value' => 'Usuário']
        ])
        ->assertCanNotSeeTableRecords([
            ['key' => 'actions.create', 'value' => 'Criar']
        ]);
});

it('can sort translations by key', function () {
    // Act
    $component = Livewire::test(ManageTranslationsPage::class);

    // Sort by key
    $component->sortTable('key');

    // Assert - Should be sorted alphabetically
    $component->assertSuccessful();

    // The default sort is already by key, so this should maintain order
    $translations = $component->get('translations');
    $keys = $translations->pluck('key')->toArray();

    expect($keys)->toBe(array_values(array_sort($keys)));
});