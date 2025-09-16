<?php

use Rodrigofs\FilamentSmartTranslate\Support\Fallback\Strategies\HumanizeStrategy;

it('transforms snake_case keys to human readable format', function () {
    $strategy = new HumanizeStrategy();

    expect($strategy->apply('user_name'))->toBe('User Name');
    expect($strategy->apply('user_profile_data'))->toBe('User Profile Data');
    expect($strategy->apply('first_name_field'))->toBe('First Name Field');
});

it('transforms camelCase keys to human readable format', function () {
    $strategy = new HumanizeStrategy();

    expect($strategy->apply('userName'))->toBe('Username');
    expect($strategy->apply('userProfileData'))->toBe('Userprofiledata');
    expect($strategy->apply('firstName'))->toBe('Firstname');
});

it('handles single words correctly', function () {
    $strategy = new HumanizeStrategy();

    expect($strategy->apply('user'))->toBe('User');
    expect($strategy->apply('name'))->toBe('Name');
    expect($strategy->apply('data'))->toBe('Data');
});

it('handles empty strings', function () {
    $strategy = new HumanizeStrategy();

    expect($strategy->apply(''))->toBe('');
});

it('handles already formatted strings', function () {
    $strategy = new HumanizeStrategy();

    expect($strategy->apply('User Name'))->toBe('User Name');
    expect($strategy->apply('Profile Data'))->toBe('Profile Data');
});

it('handles mixed case and special characters', function () {
    $strategy = new HumanizeStrategy();

    expect($strategy->apply('user_Name_Field'))->toBe('User Name Field');
    expect($strategy->apply('user-name-field'))->toBe('User Name Field');
});
