<?php

use Rodrigofs\FilamentAutoTranslate\Autoload\AutoloadManager;
use Rodrigofs\FilamentAutoTranslate\Registry\OverrideMappingRegistry;

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

uses(Tests\TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Test Helper Functions
|--------------------------------------------------------------------------
*/

if (! function_exists('package_path')) {
    function package_path(string $path = ''): string
    {
        return __DIR__ . '/../' . $path;
    }
}

// Autoload test helpers
function resetAutoloadState(): void
{
    AutoloadManager::reset();
    OverrideMappingRegistry::clear();
}

function mockFilamentClass(string $className, string $content): void
{
    eval($content);
    OverrideMappingRegistry::register($className, [
        'methods' => ['getTitleCaseModelLabel'],
        'override_path' => tempnam(sys_get_temp_dir(), 'pest_test'),
    ]);
}
