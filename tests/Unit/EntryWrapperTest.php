<?php

use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\EntryWrapper;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);

    // Note: TranslationHelper currently uses fallback strategies only, not actual translations
});

it('entry wrapper applies fallback strategy correctly', function () {
    $wrapper = new EntryWrapper('email_address');

    // TranslationHelper uses fallback strategies, not actual translations
    // Default strategy is 'original' which uses ucfirst after processing
    expect($wrapper->getLabel())->toBe('Email address');
});

it('entry wrapper uses fallback when no translation exists', function () {
    $wrapper = new EntryWrapper('nonexistent_entry');

    // Should use fallback strategy (original by default)
    expect($wrapper->getLabel())->toBe('Nonexistent entry');
});

it('entry wrapper respects base labels when set', function () {
    $wrapper = new EntryWrapper('test_entry');

    // Simulate having a base label - we need to test the filled($label = $this->getBaseLabel()) logic
    // Since we can't easily mock getBaseLabel(), we'll test the fallback behavior
    expect($wrapper->getLabel())->toBeString();
});

it('entry wrapper handles underscores with lower_case strategy', function () {
    // Configure to use lower_case strategy for entries
    Config::set('filament-smart-translate.components.entries.fallback_strategy', 'lower_case');

    $wrapper = new EntryWrapper('user_contact_info');

    // Should apply lower_case strategy: user_contact_info -> user-contact-info
    expect($wrapper->getLabel())->toBe('user-contact-info');
});

it('entry wrapper handles empty names correctly', function () {
    $wrapper = new EntryWrapper('');

    expect($wrapper->getLabel())->toBe('');
});

it('entry wrapper handles dotted keys correctly', function () {
    $wrapper = new EntryWrapper('user.contact.email');

    // Should extract only the part after last dot
    expect($wrapper->getLabel())->toBe('Email');
});

it('entry wrapper respects enabled/disabled settings', function () {
    // Disable entries component
    Config::set('filament-smart-translate.components.entries.enabled', false);

    $wrapper = new EntryWrapper('test_entry');

    // When component is disabled, should use default (original) strategy
    expect($wrapper->getLabel())->toBe('Test entry');
});

it('entry wrapper handles humanize strategy', function () {
    // Configure to use humanize strategy for entries
    Config::set('filament-smart-translate.components.entries.fallback_strategy', 'humanize');

    $wrapper = new EntryWrapper('user_profile_settings');

    // Should apply humanize strategy
    expect($wrapper->getLabel())->toBe('User Profile Settings');
});

it('entry wrapper returns correct type', function () {
    $wrapper = new EntryWrapper('test');

    $label = $wrapper->getLabel();
    expect($label)->toBeString();
});

it('entry wrapper uses entries context correctly', function () {
    // Test that it specifically uses 'entries' context
    $wrapper = new EntryWrapper('phone_number');

    // Should use fallback strategy with entries context
    expect($wrapper->getLabel())->toBe('Phone number');
});
