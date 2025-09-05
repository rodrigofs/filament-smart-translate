<?php

use Rodrigofs\FilamentSmartTranslate\Support\Fallback\TitleCaseStrategy;

it('transforms keys to title case', function () {
    $strategy = new TitleCaseStrategy();

    expect($strategy->apply('user name'))->toBe('User Name');
    expect($strategy->apply('user profile data'))->toBe('User Profile Data');
    expect($strategy->apply('first name field'))->toBe('First Name Field');
});

it('handles single words correctly', function () {
    $strategy = new TitleCaseStrategy();

    expect($strategy->apply('user'))->toBe('User');
    expect($strategy->apply('name'))->toBe('Name');
    expect($strategy->apply('data'))->toBe('Data');
});

it('handles empty strings', function () {
    $strategy = new TitleCaseStrategy();

    expect($strategy->apply(''))->toBe('');
});

it('handles mixed case input', function () {
    $strategy = new TitleCaseStrategy();

    expect($strategy->apply('uSer nAmE'))->toBe('USer NAmE');
    expect($strategy->apply('PROFILE DATA'))->toBe('PROFILE DATA');
});

it('handles special characters and separators', function () {
    $strategy = new TitleCaseStrategy();

    expect($strategy->apply('user-name field'))->toBe('User-name Field');
    expect($strategy->apply('user_name data'))->toBe('User_name Data');
    expect($strategy->apply('user.name@domain'))->toBe('User.name@domain');
});

it('handles numbers correctly', function () {
    $strategy = new TitleCaseStrategy();

    expect($strategy->apply('user 123 name'))->toBe('User 123 Name');
    expect($strategy->apply('field1 field2'))->toBe('Field1 Field2');
});

it('handles already title cased strings', function () {
    $strategy = new TitleCaseStrategy();

    expect($strategy->apply('User Name'))->toBe('User Name');
    expect($strategy->apply('Profile Data'))->toBe('Profile Data');
});

it('handles leading and trailing spaces', function () {
    $strategy = new TitleCaseStrategy();

    expect($strategy->apply('  user name  '))->toBe('  User Name  ');
    expect($strategy->apply(' profile '))->toBe(' Profile ');
});
