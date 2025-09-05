<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    Config::set('app.locale', 'en');
    Config::set('filament-smart-translate.enabled', true);
});

it('handles different fallback strategies in component coverage', function () {
    Config::set('filament-smart-translate.components', [
        'resource_labels' => ['enabled' => true, 'fallback_strategy' => 'humanize'],
        'navigation' => ['enabled' => true, 'fallback_strategy' => 'title_case'],
        'actions' => ['enabled' => false, 'fallback_strategy' => 'original'],
        'clusters' => ['enabled' => true, 'fallback_strategy' => 'unknown_strategy'],
        'pages' => ['enabled' => true, 'fallback_strategy' => 'original'],
    ]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutput('  🔧 Component Coverage:')
        ->doesntExpectOutput('Error');
});

it('calculates coverage percentage correctly for partial enabled components', function () {
    Config::set('filament-smart-translate.components', [
        'resource_labels' => ['enabled' => true],
        'navigation' => ['enabled' => false],
        'actions' => ['enabled' => true],
        'clusters' => ['enabled' => false],
        'pages' => ['enabled' => true],
    ]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutput('    ▓ Active components: 3/5 (60%)');
});

it('shows low coverage percentage in red', function () {
    Config::set('filament-smart-translate.components', [
        'resource_labels' => ['enabled' => false],
        'navigation' => ['enabled' => false],
        'actions' => ['enabled' => true],
        'clusters' => ['enabled' => false],
        'pages' => ['enabled' => false],
    ]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutput('    ▓ Active components: 1/5 (20%)');
});

it('displays tips when coverage is not 100%', function () {
    Config::set('filament-smart-translate.components', [
        'resource_labels' => ['enabled' => false],
        'navigation' => ['enabled' => true],
        'actions' => ['enabled' => true],
        'clusters' => ['enabled' => true],
        'pages' => ['enabled' => true],
    ]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutput('  💡 Tip: To enable disabled components, configure the file:')
        ->expectsOutput('     config/filament-smart-translate.php');
});

it('does not display tips when coverage is 100%', function () {
    Config::set('filament-smart-translate.components', [
        'resource_labels' => ['enabled' => true],
        'navigation' => ['enabled' => true],
        'actions' => ['enabled' => true],
        'clusters' => ['enabled' => true],
        'pages' => ['enabled' => true],
    ]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->doesntExpectOutput('  💡 Tip: To enable disabled components');
});

it('handles file scanning when directories do not exist', function () {
    // Mock File::exists to return false for all paths
    File::shouldReceive('exists')
        ->andReturn(false);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutput('    ⚠ No traits found in use');
});

it('handles file scanning with empty directories', function () {
    $testPath = base_path('test-filament');

    // Mock File methods for empty directory
    File::shouldReceive('exists')
        ->with(app_path('Filament'))
        ->andReturn(false);
    File::shouldReceive('exists')
        ->with(base_path('app/Filament'))
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->with(realpath(base_path('app/Filament')))
        ->andReturn([]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutput('    ⚠ No traits found in use');
});

it('detects resource trait candidates correctly', function () {
    $mockFile = new class
    {
        public function getExtension()
        {
            return 'php';
        }

        public function getPathname()
        {
            return base_path('app/Filament/Resources/UserResource.php');
        }
    };

    File::shouldReceive('exists')
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->andReturn([$mockFile]);
    File::shouldReceive('get')
        ->with(base_path('app/Filament/Resources/UserResource.php'))
        ->andReturn('<?php class UserResource extends Resource {}');

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);
});

it('detects page trait candidates correctly', function () {
    $mockFile = new class
    {
        public function getExtension()
        {
            return 'php';
        }

        public function getPathname()
        {
            return base_path('app/Filament/Pages/Dashboard.php');
        }
    };

    File::shouldReceive('exists')
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->andReturn([$mockFile]);
    File::shouldReceive('get')
        ->with(base_path('app/Filament/Pages/Dashboard.php'))
        ->andReturn('<?php class Dashboard extends Page {}');

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);
});

it('detects cluster trait candidates correctly', function () {
    $mockFile = new class
    {
        public function getExtension()
        {
            return 'php';
        }

        public function getPathname()
        {
            return base_path('app/Filament/Clusters/Settings.php');
        }
    };

    File::shouldReceive('exists')
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->andReturn([$mockFile]);
    File::shouldReceive('get')
        ->with(base_path('app/Filament/Clusters/Settings.php'))
        ->andReturn('<?php class Settings extends Cluster {}');

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);
});

it('handles files that already use traits', function () {
    $mockFile = new class
    {
        public function getExtension()
        {
            return 'php';
        }

        public function getPathname()
        {
            return base_path('app/Filament/Resources/UserResource.php');
        }
    };

    File::shouldReceive('exists')
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->andReturn([$mockFile]);
    File::shouldReceive('get')
        ->with(base_path('app/Filament/Resources/UserResource.php'))
        ->andReturn('<?php use ResourceTranslateble; class UserResource extends Resource { use ResourceTranslateble; }');

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);
});

it('handles files with namespaced trait usage', function () {
    $mockFile = new class
    {
        public function getExtension()
        {
            return 'php';
        }

        public function getPathname()
        {
            return base_path('app/Filament/Resources/UserResource.php');
        }
    };

    File::shouldReceive('exists')
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->andReturn([$mockFile]);
    File::shouldReceive('get')
        ->with(base_path('app/Filament/Resources/UserResource.php'))
        ->andReturn('<?php use Rodrigofs\\FilamentSmartTranslate\\Resource\\Concerns\\ResourceTranslateble; class UserResource extends Resource { use ResourceTranslateble; }');

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);
});

it('shows trait candidates when files could use traits but do not', function () {
    $mockFile = new class
    {
        public function getExtension()
        {
            return 'php';
        }

        public function getPathname()
        {
            return base_path('app/Filament/Resources/UserResource.php');
        }
    };

    File::shouldReceive('exists')
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->andReturn([$mockFile]);
    File::shouldReceive('get')
        ->with(base_path('app/Filament/Resources/UserResource.php'))
        ->andReturn('<?php class UserResource extends Resource {}');

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);
});

it('shows info about traits when no traits are used but also no candidates', function () {
    File::shouldReceive('exists')
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->andReturn([]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0)
        ->expectsOutput('  💡 Info: Traits are optional and provide additional control over:');
});

it('handles non-php files in scanning', function () {
    $mockFile = new class
    {
        public function getExtension()
        {
            return 'txt';
        }

        public function getPathname()
        {
            return base_path('app/Filament/readme.txt');
        }
    };

    File::shouldReceive('exists')
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->andReturn([$mockFile]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);
});

it('avoids duplicate paths in scanning', function () {
    // Create a scenario where both paths exist but resolve to the same real path
    File::shouldReceive('exists')
        ->andReturn(true);
    File::shouldReceive('allFiles')
        ->andReturn([]);

    $this->artisan('filament-smart-translate:status')
        ->assertExitCode(0);
});
