<?php

use Rodrigofs\FilamentSmartTranslate\Pages\TranslationManagerPage;
use Rodrigofs\FilamentSmartTranslate\Services\TranslationService;

beforeEach(function () {
    $this->tempPath = sys_get_temp_dir() . '/translation_page_test_' . uniqid();
    mkdir($this->tempPath, 0755, true);

    // Create test translation files
    file_put_contents("{$this->tempPath}/pt_BR.json", json_encode([
        'resources.user' => 'Usuário',
        'resources.product' => 'Produto',
        'actions.create' => 'Criar',
        'validation.required' => 'Campo obrigatório',
        'empty.field' => '',
    ]));

    file_put_contents("{$this->tempPath}/en.json", json_encode([
        'resources.user' => 'User',
        'resources.product' => 'Product',
        'actions.create' => 'Create',
    ]));

    // Create service with test path
    $this->service = new TranslationService($this->tempPath);
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

it('can instantiate manage translations page', function () {
    // Test that the page can be instantiated without errors
    $page = new TranslationManagerPage();

    expect($page)->toBeInstanceOf(TranslationManagerPage::class);

    // Set locale manually and test
    $page->locale = 'pt_BR';
    expect($page->locale)->toBe('pt_BR');
});

it('loads translations correctly', function () {
    // Create a page instance and manually call the setup
    $page = new TranslationManagerPage();
    $page->locale = 'pt_BR';

    // Inject our test service
    $reflection = new ReflectionClass($page);
    $property = $reflection->getProperty('translationService');
    $property->setAccessible(true);
    $property->setValue($page, $this->service);

    // Test the loadTranslations method
    $method = $reflection->getMethod('loadTranslations');
    $method->setAccessible(true);
    $method->invoke($page);

    expect($page->translations)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($page->translations->count())->toBe(5);

    $userTranslation = $page->translations->firstWhere('key', 'resources.user');
    expect($userTranslation['value'])->toBe('Usuário');
    expect($userTranslation['category'])->toBe('resources');
});

it('loads statistics correctly', function () {
    $page = new TranslationManagerPage();
    $page->locale = 'pt_BR';

    // Inject our test service
    $reflection = new ReflectionClass($page);
    $property = $reflection->getProperty('translationService');
    $property->setAccessible(true);
    $property->setValue($page, $this->service);

    // Test the loadStatistics method
    $method = $reflection->getMethod('loadStatistics');
    $method->setAccessible(true);
    $method->invoke($page);

    expect($page->statistics)->toBeArray();
    expect($page->statistics['total'])->toBe(5);
    expect($page->statistics['empty'])->toBe(1); // empty.field
});

it('can add translations through service', function () {
    $page = new TranslationManagerPage();

    // Inject our test service
    $reflection = new ReflectionClass($page);
    $property = $reflection->getProperty('translationService');
    $property->setAccessible(true);
    $property->setValue($page, $this->service);

    // Test addTranslation method
    $page->addTranslation([
        'locale' => 'pt_BR',
        'key' => 'test.new',
        'value' => 'Novo Teste',
    ]);

    // Verify the translation was added
    $translations = $this->service->loadTranslations('pt_BR');
    expect($translations->has('test.new'))->toBeTrue();
    expect($translations->get('test.new'))->toBe('Novo Teste');
});

it('can update translations through service', function () {
    $page = new TranslationManagerPage();

    // Inject our test service
    $reflection = new ReflectionClass($page);
    $property = $reflection->getProperty('translationService');
    $property->setAccessible(true);
    $property->setValue($page, $this->service);

    // Test updateTranslation method
    $page->updateTranslation('pt_BR', 'resources.user', 'Usuário Atualizado', 'pt_BR');

    // Verify the translation was updated
    $translations = $this->service->loadTranslations('pt_BR');
    expect($translations->get('resources.user'))->toBe('Usuário Atualizado');
});

it('can delete translations through service', function () {
    $page = new TranslationManagerPage();

    // Inject our test service
    $reflection = new ReflectionClass($page);
    $property = $reflection->getProperty('translationService');
    $property->setAccessible(true);
    $property->setValue($page, $this->service);

    // Test deleteTranslation method
    $page->deleteTranslation('pt_BR', 'resources.product');

    // Verify the translation was deleted
    $translations = $this->service->loadTranslations('pt_BR');
    expect($translations->has('resources.product'))->toBeFalse();
});

it('can bulk delete translations through service', function () {
    $page = new TranslationManagerPage();

    // Inject our test service
    $reflection = new ReflectionClass($page);
    $property = $reflection->getProperty('translationService');
    $property->setAccessible(true);
    $property->setValue($page, $this->service);

    // Test bulkDeleteTranslations method
    $records = [
        ['key' => 'resources.user', 'locale' => 'pt_BR'],
        ['key' => 'actions.create', 'locale' => 'pt_BR'],
    ];

    $page->bulkDeleteTranslations($records);

    // Verify the translations were deleted
    $translations = $this->service->loadTranslations('pt_BR');
    expect($translations->has('resources.user'))->toBeFalse();
    expect($translations->has('actions.create'))->toBeFalse();
    expect($translations->has('resources.product'))->toBeTrue(); // Should still exist
});

it('handles refresh correctly', function () {
    $page = new TranslationManagerPage();
    $page->locale = 'pt_BR';

    // Inject our test service
    $reflection = new ReflectionClass($page);
    $property = $reflection->getProperty('translationService');
    $property->setAccessible(true);
    $property->setValue($page, $this->service);

    // Initial load
    $method = $reflection->getMethod('loadTranslations');
    $method->setAccessible(true);
    $method->invoke($page);

    $initialCount = $page->translations->count();

    // Modify file externally
    $translations = $this->service->loadTranslations('pt_BR');
    $translations->put('external.addition', 'Added Externally');
    $this->service->saveTranslations('pt_BR', $translations);

    // Test refresh
    $page->refreshTable();

    expect($page->translations->count())->toBe($initialCount + 1);
    expect($page->translations->firstWhere('key', 'external.addition')['value'])->toBe('Added Externally');
});
