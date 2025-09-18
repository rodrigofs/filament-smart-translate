<?php

use Illuminate\Support\Facades\App;
use Rodrigofs\FilamentSmartTranslate\Pages\TranslationManagerPage;

it('loads English translations correctly', function () {
    App::setLocale('en');

    expect(__('filament-smart-translate::translation-manager.page_title'))->toBe('Manage Translations');
    expect(__('filament-smart-translate::translation-manager.navigation_label'))->toBe('Translations');
    expect(__('filament-smart-translate::translation-manager.columns.key'))->toBe('Translation Key');
    expect(__('filament-smart-translate::translation-manager.actions.new_translation'))->toBe('New Translation');
    expect(__('filament-smart-translate::translation-manager.notifications.translation_added.title'))->toBe('Translation added');
});

it('loads Portuguese translations correctly', function () {
    App::setLocale('pt_BR');

    expect(__('filament-smart-translate::translation-manager.page_title'))->toBe('Gerenciar Traduções');
    expect(__('filament-smart-translate::translation-manager.navigation_label'))->toBe('Traduções');
    expect(__('filament-smart-translate::translation-manager.columns.key'))->toBe('Chave da Tradução');
    expect(__('filament-smart-translate::translation-manager.actions.new_translation'))->toBe('Nova Tradução');
    expect(__('filament-smart-translate::translation-manager.notifications.translation_added.title'))->toBe('Tradução adicionada');
});

it('translation manager page uses translated titles', function () {
    App::setLocale('pt_BR');

    $page = new TranslationManagerPage();

    expect($page->getTitle())->toBe('Gerenciar Traduções');
    expect(TranslationManagerPage::getNavigationLabel())->toBe('Traduções');
});

it('falls back to English when translation is missing', function () {
    App::setLocale('es'); // Spanish - not implemented

    // Should fall back to English
    expect(__('filament-smart-translate::translation-manager.page_title'))->toBe('Manage Translations');
    expect(__('filament-smart-translate::translation-manager.navigation_label'))->toBe('Translations');
});

it('trans method works correctly in page', function () {
    App::setLocale('pt_BR');

    $page = new TranslationManagerPage();

    // Use reflection to access protected method
    $reflection = new ReflectionClass($page);
    $transMethod = $reflection->getMethod('trans');
    $transMethod->setAccessible(true);

    expect($transMethod->invoke($page, 'page_title'))->toBe('Gerenciar Traduções');
    expect($transMethod->invoke($page, 'actions.edit'))->toBe('Editar');

    // Test with replacements
    expect($transMethod->invoke($page, 'notifications.translation_added.body', [
        'key' => 'test.key',
        'locale' => 'pt_BR',
    ]))->toBe('A tradução \'test.key\' foi adicionada com sucesso para o idioma \'pt_BR\'.');
});
