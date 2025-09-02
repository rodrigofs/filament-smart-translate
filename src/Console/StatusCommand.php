<?php

namespace Rodrigofs\FilamentAutoTranslate\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class StatusCommand extends Command
{
    protected $signature = 'filament-auto-translation:status';

    protected $description = 'Show the package coverage status and trait usage';

    public function handle(): int
    {
        $this->displayHeader();
        $this->displayPackageStatus();
        $this->displayTraitUsage();
        $this->displayComponentCoverage();
        $this->displaySummary();

        return self::SUCCESS;
    }

    private function displayHeader(): void
    {
        $this->newLine();
        $this->line('  <fg=cyan>╔══════════════════════════════════════════════════════════╗</fg=cyan>');
        $this->line('  <fg=cyan>║</fg=cyan>  <fg=white;bg=cyan> Filament Auto Translation - Status Report </fg=white;bg=cyan>  <fg=cyan>║</fg=cyan>');
        $this->line('  <fg=cyan>╚══════════════════════════════════════════════════════════╝</fg=cyan>');
        $this->newLine();
    }

    private function displayPackageStatus(): void
    {
        $enabled = Config::get('filament-auto-translation.enabled', true);
        $status = $enabled ? '<fg=green>✓ ENABLED</fg=green>' : '<fg=red>✗ DISABLED</fg=red>';

        $this->line("  📦 <fg=white;options=bold>Package Status:</fg=white;options=bold> {$status}");
        $this->newLine();
    }

    private function displayTraitUsage(): void
    {
        $this->line('  🎯 <fg=white;options=bold>Trait Usage:</fg=white;options=bold>');
        $this->newLine();

        $traits = $this->scanForTraitUsage();
        $candidates = $this->scanForTraitCandidates();

        if (empty($traits)) {
            $this->line('    <fg=yellow>⚠ No traits found in use</fg=yellow>');
        } else {
            foreach ($traits as $trait => $files) {
                $count = count($files);
                $this->line("    <fg=green>✓</fg=green> <fg=white>{$trait}</fg=white> <fg=gray>({$count} file" . ($count > 1 ? 's' : '') . ')</fg=gray>');

                foreach ($files as $file) {
                    $this->line("      <fg=gray>└─</fg=gray> {$file}");
                }
            }
        }

        // Show candidates that could use traits but don't
        $this->displayTraitCandidates($traits, $candidates);

        $this->newLine();
    }

    private function displayComponentCoverage(): void
    {
        $this->line('  🔧 <fg=white;options=bold>Component Coverage:</fg=white;options=bold>');
        $this->newLine();

        $components = [
            'resource_labels' => 'Resource Labels',
            'navigation' => 'Navigation',
            'actions' => 'Actions',
            'clusters' => 'Clusters',
            'pages' => 'Pages',
        ];

        foreach ($components as $key => $label) {
            $enabled = Config::get("filament-auto-translation.components.{$key}.enabled", true);
            $fallback = Config::get("filament-auto-translation.components.{$key}.fallback_strategy", 'original');

            $status = $enabled ? '<fg=green>✓</fg=green>' : '<fg=red>✗</fg=red>';
            $fallbackColor = match ($fallback) {
                'humanize' => 'yellow',
                'title_case' => 'blue',
                'original' => 'gray',
                default => 'magenta'
            };

            $this->line("    {$status} <fg=white>{$label}</fg=white> <fg={$fallbackColor}>({$fallback})</fg={$fallbackColor}>");
        }
        $this->newLine();
    }

    private function displaySummary(): void
    {
        $this->line('  📊 <fg=white;options=bold>Coverage Summary:</fg=white;options=bold>');
        $this->newLine();

        $components = ['resource_labels', 'navigation', 'actions', 'clusters', 'pages'];
        $enabled = array_filter($components, fn ($key) => Config::get("filament-auto-translation.components.{$key}.enabled", true));
        $coverage = count($enabled);
        $total = count($components);
        $percentage = round(($coverage / $total) * 100);

        $color = match (true) {
            $percentage >= 80 => 'green',
            $percentage >= 60 => 'yellow',
            default => 'red'
        };

        $this->line("    <fg={$color}>▓</fg={$color}> Active components: <fg=white;options=bold>{$coverage}/{$total}</fg=white;options=bold> <fg={$color}>({$percentage}%)</fg={$color}>");

        $traits = $this->scanForTraitUsage();
        $candidates = $this->scanForTraitCandidates();
        $traitCount = array_sum(array_map('count', $traits));

        // Count candidates that don't use traits
        $candidatesCount = 0;
        foreach ($candidates as $trait => $files) {
            $unusedFiles = array_diff($files, $traits[$trait] ?? []);
            $candidatesCount += count($unusedFiles);
        }

        if ($traitCount > 0) {
            $this->line("    <fg=green>▓</fg=green> Implemented traits: <fg=white;options=bold>{$traitCount}</fg=white;options=bold> files");
        } else {
            $this->line('    <fg=yellow>▓</fg=yellow> Implemented traits: <fg=white;options=bold>0</fg=white;options=bold> files <fg=gray>(optional)</fg=gray>');
        }

        if ($candidatesCount > 0) {
            $this->line("    <fg=yellow>▓</fg=yellow> Candidates without traits: <fg=white;options=bold>{$candidatesCount}</fg=white;options=bold> files <fg=gray>(could use traits)</fg=gray>");
        }

        $this->newLine();

        if ($percentage < 100) {
            $this->line('  💡 <fg=yellow>Tip:</fg=yellow> To enable disabled components, configure the file:');
            $this->line('     <fg=gray>config/filament-auto-translation.php</fg=gray>');
            $this->newLine();
        }

        if ($candidatesCount > 0) {
            $this->line('  💡 <fg=blue>Tip:</fg=blue> For better control, consider adding traits to candidates:');
            $this->line('     <fg=gray>• ResourceTranslateble - For resources with custom model labels</fg=gray>');
            $this->line('     <fg=gray>• PageTranslateble - For pages with navigation groups</fg=gray>');
            $this->line('     <fg=gray>• ClusterTranslateble - For clusters with custom breadcrumbs</fg=gray>');
            $this->newLine();
        } elseif ($traitCount === 0) {
            $this->line('  💡 <fg=blue>Info:</fg=blue> Traits are optional and provide additional control over:');
            $this->line('     <fg=gray>• ResourceTranslateble - Model labels in resources</fg=gray>');
            $this->line('     <fg=gray>• PageTranslateble - Navigation groups in pages</fg=gray>');
            $this->line('     <fg=gray>• ClusterTranslateble - Cluster breadcrumbs</fg=gray>');
            $this->newLine();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function scanForTraitUsage(): array
    {
        $traits = [
            'ResourceTranslateble' => [],
            'PageTranslateble' => [],
            'ClusterTranslateble' => [],
        ];

        // Use unique real paths to avoid duplicates from different path expressions
        $searchPaths = array_unique(array_filter([
            app_path('Filament'),
            base_path('app/Filament'),
        ], fn ($path) => File::exists($path)));

        // Resolve real paths to avoid processing same directory twice
        $realPaths = [];
        foreach ($searchPaths as $path) {
            $realPath = realpath($path);
            if ($realPath && ! in_array($realPath, $realPaths)) {
                $realPaths[] = $realPath;
                $this->scanDirectory($realPath, $traits);
            }
        }

        // Remove duplicates and sort
        foreach ($traits as $trait => $files) {
            $traits[$trait] = array_unique($files);
            sort($traits[$trait]);
        }

        return array_filter($traits, fn ($files) => ! empty($files));
    }

    /**
     * @param  array<string,mixed>  $traits
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function scanDirectory(string $path, array &$traits): void
    {
        $files = File::allFiles($path);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = File::get($file->getPathname());
                $relativePath = str_replace(base_path() . '/', '', $file->getPathname());

                foreach ($traits as $trait => $currentFiles) {
                    // More precise regex to match exact trait usage
                    $patterns = [
                        '/use\s+[^;]*\\\\' . preg_quote($trait, '/') . '\s*;/',  // Namespaced trait
                        '/use\s+' . preg_quote($trait, '/') . '\s*;/',            // Direct trait
                    ];

                    $found = false;
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $content)) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found && ! in_array($relativePath, $traits[$trait])) {
                        $traits[$trait][] = $relativePath;
                    }
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function scanForTraitCandidates(): array
    {
        $candidates = [
            'ResourceTranslateble' => [],
            'PageTranslateble' => [],
            'ClusterTranslateble' => [],
        ];

        // Use unique real paths to avoid duplicates from different path expressions
        $searchPaths = array_unique(array_filter([
            app_path('Filament'),
            base_path('app/Filament'),
        ], fn ($path) => File::exists($path)));

        // Resolve real paths to avoid processing same directory twice
        $realPaths = [];
        foreach ($searchPaths as $path) {
            $realPath = realpath($path);
            if ($realPath && ! in_array($realPath, $realPaths)) {
                $realPaths[] = $realPath;
                $this->scanCandidatesDirectory($realPath, $candidates);
            }
        }

        foreach ($candidates as $trait => $files) {
            $candidates[$trait] = array_unique($files);
            sort($candidates[$trait]);
        }

        return $candidates;
    }

    /**
     * @param  array<string, mixed>  $candidates
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function scanCandidatesDirectory(string $path, array &$candidates): void
    {
        $files = File::allFiles($path);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = File::get($file->getPathname());
                $relativePath = str_replace(base_path() . '/', '', $file->getPathname());

                // Check for Resources
                if (str_contains($content, 'extends Resource')) {
                    $hasResourceTrait = preg_match('/use\s+[^;]*\\\\ResourceTranslateble\s*;/', $content) ||
                                       preg_match('/use\s+ResourceTranslateble\s*;/', $content);

                    if (! $hasResourceTrait && ! in_array($relativePath, $candidates['ResourceTranslateble'])) {
                        $candidates['ResourceTranslateble'][] = $relativePath;
                    }
                }

                // Check for Pages
                if (str_contains($content, 'extends Page')) {
                    $hasPageTrait = preg_match('/use\s+[^;]*\\\\PageTranslateble\s*;/', $content) ||
                                   preg_match('/use\s+PageTranslateble\s*;/', $content);

                    if (! $hasPageTrait && ! in_array($relativePath, $candidates['PageTranslateble'])) {
                        $candidates['PageTranslateble'][] = $relativePath;
                    }
                }

                // Check for Clusters
                if (str_contains($content, 'extends Cluster')) {
                    $hasClusterTrait = preg_match('/use\s+[^;]*\\\\ClusterTranslateble\s*;/', $content) ||
                                      preg_match('/use\s+ClusterTranslateble\s*;/', $content);

                    if (! $hasClusterTrait && ! in_array($relativePath, $candidates['ClusterTranslateble'])) {
                        $candidates['ClusterTranslateble'][] = $relativePath;
                    }
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $usedTraits
     * @param  array<string, mixed>  $candidates
     */
    private function displayTraitCandidates(array $usedTraits, array $candidates): void
    {
        $hasAnyCandidates = false;

        foreach ($candidates as $trait => $files) {
            // Remove files that already use the trait
            $unusedFiles = array_diff($files, $usedTraits[$trait] ?? []);

            if (! empty($unusedFiles)) {
                if (! $hasAnyCandidates) {
                    $this->newLine();
                    $this->line('    <fg=yellow>⚠ Files that could use traits:</fg=yellow>');
                    $hasAnyCandidates = true;
                }

                $count = count($unusedFiles);
                $this->line("    <fg=yellow>○</fg=yellow> <fg=white>{$trait}</fg=white> <fg=gray>({$count} candidate" . ($count > 1 ? 's' : '') . ')</fg=gray>');

                foreach ($unusedFiles as $file) {
                    $this->line("      <fg=gray>└─</fg=gray> {$file}");
                }
            }
        }
    }
}
