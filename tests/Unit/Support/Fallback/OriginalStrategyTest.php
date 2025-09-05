<?php

use Rodrigofs\FilamentSmartTranslate\Support\Fallback\OriginalStrategy;

it('returns the original key unchanged', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply('user_name'))->toBe('user_name');
    expect($strategy->apply('userName'))->toBe('userName');
    expect($strategy->apply('User Name'))->toBe('User Name');
});

it('handles empty strings', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply(''))->toBe('');
});

it('handles special characters and numbers', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply('user-name_123'))->toBe('user-name_123');
    expect($strategy->apply('email@domain.com'))->toBe('email@domain.com');
    expect($strategy->apply('!@#$%^&*()'))->toBe('!@#$%^&*()');
});

it('handles whitespace correctly', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply('  spaces  '))->toBe('  spaces  ');
    expect($strategy->apply("\t\n\r"))->toBe("\t\n\r");
});

it('handles unicode characters', function () {
    $strategy = new OriginalStrategy();

    expect($strategy->apply('usuário_nome'))->toBe('usuário_nome');
    expect($strategy->apply('测试用户'))->toBe('测试用户');
});
