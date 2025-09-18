<?php

use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyManager;

it('transforms keys to title case', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    expect($strategy->apply('user_name'))->toBe('user-name');
    expect($strategy->apply('user-profile-data'))->toBe('user-profile-data');
    expect($strategy->apply('first_name_field'))->toBe('first-name-field');
});

it('handles single words correctly', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    expect($strategy->apply('user'))->toBe('user');
    expect($strategy->apply('name'))->toBe('name');
    expect($strategy->apply('data'))->toBe('data');
});

it('handles empty strings', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    expect($strategy->apply(''))->toBe('');
});

it('handles mixed case input', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    expect($strategy->apply('uSer_nAmE'))->toBe('user-name');
    expect($strategy->apply('PROFILE-DATA'))->toBe('profile-data');
});

it('handles special characters and separators', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    expect($strategy->apply('user-name_field'))->toBe('user-name-field');
    expect($strategy->apply('user_name-data'))->toBe('user-name-data');
    expect($strategy->apply('user.name@domain'))->toBe('name@domain');
});

it('handles dotted keys correctly', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    expect($strategy->apply('module.user_name'))->toBe('user-name'); // Takes after last dot
    expect($strategy->apply('app.forms.field_name'))->toBe('field-name');
});

it('handles already formatted strings', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    // title_case is an alias for lower_case, so it converts to lower-case with hyphens
    expect($strategy->apply('User Name'))->toBe('user-name');
    expect($strategy->apply('Profile Data'))->toBe('profile-data');
});

it('handles special cases', function () {
    $strategy = FallbackStrategyManager::resolve('title_case');

    expect($strategy->apply('user name'))->toBe('user-name'); // Spaces become dashes
    expect($strategy->apply(' profile '))->toBe('-profile-'); // Leading/trailing spaces become dashes
});
