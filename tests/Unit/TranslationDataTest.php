<?php

use Rodrigofs\FilamentSmartTranslate\Data\TranslationData;

it('can be created with constructor', function () {
    // Act
    $translation = new TranslationData('test.key', 'Test Value', 'pt_BR');

    // Assert
    expect($translation->key)->toBe('test.key');
    expect($translation->value)->toBe('Test Value');
    expect($translation->locale)->toBe('pt_BR');
});

it('can be created from array', function () {
    // Setup
    $data = [
        'key' => 'test.key',
        'value' => 'Test Value',
        'locale' => 'pt_BR',
    ];

    // Act
    $translation = TranslationData::fromArray($data);

    // Assert
    expect($translation->key)->toBe('test.key');
    expect($translation->value)->toBe('Test Value');
    expect($translation->locale)->toBe('pt_BR');
});

it('can be converted to array', function () {
    // Setup
    $translation = new TranslationData('test.key', 'Test Value', 'pt_BR');

    // Act
    $array = $translation->toArray();

    // Assert
    expect($array)->toBe([
        'key' => 'test.key',
        'value' => 'Test Value',
        'locale' => 'pt_BR',
    ]);
});

it('calculates length correctly', function () {
    // Setup
    $translation = new TranslationData('test.key', 'Hello World', 'pt_BR');

    // Act & Assert
    expect($translation->getLength())->toBe(11);
});

it('detects empty translations', function () {
    // Setup
    $empty = new TranslationData('empty.key', '', 'pt_BR');
    $whitespace = new TranslationData('whitespace.key', '   ', 'pt_BR');
    $nonEmpty = new TranslationData('non.empty.key', 'Value', 'pt_BR');

    // Act & Assert
    expect($empty->isEmpty())->toBeTrue();
    expect($whitespace->isEmpty())->toBeTrue();
    expect($nonEmpty->isEmpty())->toBeFalse();
});

it('detects long translations', function () {
    // Setup
    $short = new TranslationData('short.key', 'Short', 'pt_BR');
    $long = new TranslationData('long.key', str_repeat('a', 101), 'pt_BR');
    $exactly100 = new TranslationData('hundred.key', str_repeat('a', 100), 'pt_BR');

    // Act & Assert
    expect($short->isLong())->toBeFalse();
    expect($long->isLong())->toBeTrue();
    expect($exactly100->isLong())->toBeFalse();
});

it('categorizes translations correctly', function () {
    $testCases = [
        // Auth category
        ['password.field', 'auth'],
        ['login.button', 'auth'],
        ['auth.failed', 'auth'],
        ['auth.success', 'auth'],

        // Validation category
        ['validation.required', 'validation'],
        ['name.must be', 'validation'],
        ['email.required', 'validation'],

        // Error category
        ['error.message', 'error'],
        ['forbidden.access', 'error'],
        ['system.error', 'error'],
        ['critical.error', 'error'],

        // UI category
        ['button.save', 'ui'],
        ['cancel.action', 'ui'],
        ['save.changes', 'ui'],

        // Component categories
        ['resources.user', 'resources'],
        ['navigations.menu', 'navigations'],
        ['actions.create', 'actions'],
        ['clusters.admin', 'clusters'],
        ['pages.dashboard', 'pages'],
        ['fields.name', 'fields'],
        ['schemas.form', 'schemas'],
        ['entries.text', 'entries'],
        ['columns.title', 'columns'],

        // General category (default)
        ['random.key', 'general'],
        ['some.other.key', 'general'],
    ];

    foreach ($testCases as [$key, $expectedCategory]) {
        $translation = new TranslationData($key, 'Value', 'pt_BR');
        expect($translation->getCategory())
            ->toBe($expectedCategory)
            ->and("Key: {$key} should be categorized as {$expectedCategory}");
    }
});

it('handles special characters in categorization', function () {
    // Setup
    $translation = new TranslationData('RESOURCES.USER', 'Value', 'pt_BR');

    // Act & Assert (should handle case insensitive matching)
    expect($translation->getCategory())->toBe('resources');
});

it('readonly properties cannot be modified', function () {
    // Setup
    $translation = new TranslationData('test.key', 'Test Value', 'pt_BR');

    // This should cause a compile-time error, but we can test the readonly behavior
    expect($translation->key)->toBe('test.key');
    expect($translation->value)->toBe('Test Value');
    expect($translation->locale)->toBe('pt_BR');

    // Properties should be accessible but not modifiable
    $reflection = new ReflectionClass($translation);
    $properties = $reflection->getProperties();

    foreach ($properties as $property) {
        expect($property->isReadOnly())->toBeTrue();
    }
});

it('handles unicode characters correctly', function () {
    // Setup
    $translation = new TranslationData('unicode.key', 'Olá! Como está? 🌟', 'pt_BR');

    // Act & Assert
    expect($translation->getLength())->toBe(mb_strlen('Olá! Como está? 🌟'));
    expect($translation->isEmpty())->toBeFalse();
    expect($translation->getCategory())->toBe('general');
});

it('handles very long keys in categorization', function () {
    // Setup - test with very long key that contains category identifier
    $longKey = 'very.long.key.with.many.segments.resources.user.and.more.segments.here';
    $translation = new TranslationData($longKey, 'Value', 'pt_BR');

    // Act & Assert - should still find the category within the long key
    expect($translation->getCategory())->toBe('resources');
});

it('handles empty key gracefully', function () {
    // Setup
    $translation = new TranslationData('', 'Value', 'pt_BR');

    // Act & Assert
    expect($translation->key)->toBe('');
    expect($translation->getCategory())->toBe('general');
    expect($translation->getLength())->toBe(5); // Length of "Value"
});

it('handles mixed case in category detection', function () {
    $testCases = [
        'Resources.User' => 'resources',
        'ACTIONS.create' => 'actions',
        'Validation.Required' => 'validation',
        'AUTH.Login' => 'auth',
    ];

    foreach ($testCases as $key => $expectedCategory) {
        $translation = new TranslationData($key, 'Value', 'pt_BR');
        expect($translation->getCategory())->toBe($expectedCategory);
    }
});
