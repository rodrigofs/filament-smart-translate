<?php

use Rodrigofs\FilamentSmartTranslate\Support\Fallback\Strategies\OriginalStrategy;

it('transforms keys to readable format', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply('user_name'))->toBe('User name');
    expect($strategy->apply('userName'))->toBe('UserName');
    expect($strategy->apply('User Name'))->toBe('User Name');
});

it('handles empty strings', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply(''))->toBe('');
});

it('handles special characters and numbers', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply('user-name_123'))->toBe('User name 123');
    expect($strategy->apply('email@domain.com'))->toBe('Com');
    expect($strategy->apply('!@#$%^&*()'))->toBe('!@#$%^&*()');
});

it('handles whitespace correctly', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply('  spaces  '))->toBe('  spaces  ');
    expect($strategy->apply("\t\n\r"))->toBe("\t\n\r");
});

it('handles unicode characters', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply('usuário_nome'))->toBe('Usuário nome');
    expect($strategy->apply('测试用户'))->toBe('测试用户');
});
