<?php

namespace Rodrigofs\FilamentSmartTranslate\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

class TranslationService
{
    protected string $basePath;
    protected array $availableLocales;
    protected array $cache = [];
    protected array $pendingWrites = [];

    public function __construct(
        string $basePath = null,
        array $availableLocales = ['pt_BR', 'en', 'es', 'fr']
    ) {
        $this->basePath = $basePath ?? base_path('lang');
        $this->availableLocales = $availableLocales;
    }

    public function loadTranslations(string $locale): Collection
    {
        $this->validateLocale($locale);

        // Check cache first
        if (isset($this->cache[$locale])) {
            return $this->cache[$locale];
        }

        $file = $this->getFilePath($locale);

        if (!File::exists($file)) {
            $this->cache[$locale] = collect([]);
            return $this->cache[$locale];
        }

        $content = File::get($file);
        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException("Error decoding JSON for locale {$locale}: " . $e->getMessage());
        }

        $this->cache[$locale] = collect($data ?? []);
        return $this->cache[$locale];
    }

    public function saveTranslations(string $locale, Collection $translations): void
    {
        $this->validateLocale($locale);
        $this->ensureDirectoryExists();

        // Update cache
        $this->cache[$locale] = $translations;

        $file = $this->getFilePath($locale);

        // Sort by key for consistency
        $sortedTranslations = $translations->sortKeys();

        $json = json_encode(
            $sortedTranslations->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        // Use file locking for concurrent access
        $lockFile = $file . '.lock';
        $lockHandle = fopen($lockFile, 'w');

        if (!flock($lockHandle, LOCK_EX)) {
            fclose($lockHandle);
            throw new RuntimeException("Could not acquire file lock for locale {$locale}");
        }

        try {
            if (!File::put($file, $json)) {
                throw new RuntimeException("Error saving translation file for locale {$locale}");
            }
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            @unlink($lockFile);
        }
    }

    /**
     * Adiciona uma nova tradução
     */
    public function addTranslation(string $locale, string $key, string $value): Collection
    {
        $translations = $this->loadTranslations($locale);

        if ($translations->has($key)) {
            throw new InvalidArgumentException("The key '{$key}' already exists");
        }

        $translations->put($key, $value);
        $this->saveTranslations($locale, $translations);

        return $translations;
    }

    /**
     * Atualiza uma tradução existente
     */
    public function updateTranslation(string $locale, string $key, string $value): Collection
    {
        $translations = $this->loadTranslations($locale);

        if (!$translations->has($key)) {
            throw new InvalidArgumentException("The key '{$key}' does not exist");
        }

        $translations->put($key, $value);
        $this->saveTranslations($locale, $translations);

        return $translations;
    }

    /**
     * Remove uma tradução
     */
    public function deleteTranslation(string $locale, string $key): Collection
    {
        $translations = $this->loadTranslations($locale);

        if (!$translations->has($key)) {
            throw new InvalidArgumentException("The key '{$key}' does not exist");
        }

        $translations->forget($key);
        $this->saveTranslations($locale, $translations);

        return $translations;
    }

    /**
     * Remove multiple translations efficiently
     */
    public function bulkDeleteTranslations(string $locale, array $keys): array
    {
        $translations = $this->loadTranslations($locale);
        $deleted = [];
        $notFound = [];

        foreach ($keys as $key) {
            if ($translations->has($key)) {
                $translations->forget($key);
                $deleted[] = $key;
            } else {
                $notFound[] = $key;
            }
        }

        if (!empty($deleted)) {
            $this->saveTranslations($locale, $translations);
        }

        return $notFound; // Return keys that were not found for error handling
    }

    /**
     * Clear cache for a specific locale or all locales
     */
    public function clearCache(?string $locale = null): void
    {
        if ($locale === null) {
            $this->cache = [];
        } else {
            unset($this->cache[$locale]);
        }
    }

    /**
     * Batch update multiple translations for better performance
     */
    public function batchUpdateTranslations(string $locale, array $updates): Collection
    {
        $translations = $this->loadTranslations($locale);

        foreach ($updates as $key => $value) {
            $translations->put($key, $value);
        }

        $this->saveTranslations($locale, $translations);
        return $translations;
    }

    /**
     * Import translations from array
     */
    public function importTranslations(
        string $locale,
        array $data,
        string $mode = 'merge'
    ): array {
        $translations = $this->loadTranslations($locale);
        $imported = 0;
        $skipped = 0;

        foreach ($data as $key => $value) {
            $shouldImport = match ($mode) {
                'merge' => true,
                'replace' => true,
                'add_only' => !$translations->has($key),
                default => throw new InvalidArgumentException("Modo de importação inválido: {$mode}"),
            };

            if ($shouldImport) {
                $translations->put($key, $value);
                $imported++;
            } else {
                $skipped++;
            }
        }

        $this->saveTranslations($locale, $translations);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'total' => count($data),
        ];
    }

    /**
     * Exporta traduções selecionadas
     */
    public function exportTranslations(string $locale, array $keys = null): array
    {
        $translations = $this->loadTranslations($locale);

        if ($keys === null) {
            return $translations->toArray();
        }

        return $translations->only($keys)->toArray();
    }

    /**
     * Obtém estatísticas das traduções
     */
    public function getStatistics(string $locale): array
    {
        $translations = $this->loadTranslations($locale);

        if ($translations->isEmpty()) {
            return [
                'total' => 0,
                'empty' => 0,
                'long' => 0,
                'average_length' => 0,
                'categories' => [],
            ];
        }

        $empty = $translations->filter(fn($value) => empty($value))->count();
        $long = $translations->filter(fn($value) => strlen($value) > 100)->count();
        $avgLength = round($translations->map(fn($value) => strlen($value))->avg());

        // Categorização automática
        $categories = $translations->keys()
            ->map(fn($key) => $this->categorizeTranslation($key))
            ->countBy()
            ->toArray();

        return [
            'total' => $translations->count(),
            'empty' => $empty,
            'long' => $long,
            'average_length' => $avgLength,
            'categories' => $categories,
        ];
    }

    /**
     * Categoriza uma tradução baseada em sua chave
     */
    public function categorizeTranslation(string $key): string
    {
        $key = strtolower($key);

        return match (true) {
            str_contains($key, 'password') || str_contains($key, 'login') || str_contains($key, 'auth') => 'auth',
            str_contains($key, 'validation') || str_contains($key, 'must be') || str_contains($key, 'required') => 'validation',
            str_contains($key, 'error') || str_contains($key, 'forbidden') || str_contains($key, 'unauthorized') => 'error',
            str_contains($key, 'button') || str_contains($key, 'cancel') || str_contains($key, 'save') => 'ui',
            default => 'general',
        };
    }

    /**
     * Busca traduções por padrão
     */
    public function searchTranslations(string $locale, string $search): Collection
    {
        $translations = $this->loadTranslations($locale);

        return $translations->filter(function ($value, $key) use ($search) {
            return str_contains(strtolower($key), strtolower($search)) ||
                str_contains(strtolower($value), strtolower($search));
        });
    }

    /**
     * Valida se um locale é suportado
     */
    protected function validateLocale(string $locale): void
    {
        if (!in_array($locale, $this->availableLocales)) {
            throw new InvalidArgumentException("Locale não suportado: {$locale}");
        }
    }

    /**
     * Obtém o caminho do arquivo para um locale
     */
    protected function getFilePath(string $locale): string
    {
        return "{$this->basePath}/{$locale}.json";
    }

    /**
     * Garante que o diretório existe
     */
    protected function ensureDirectoryExists(): void
    {
        if (!File::isDirectory($this->basePath)) {
            File::makeDirectory($this->basePath, 0755, true);
        }
    }

    /**
     * Obtém todos os locales disponíveis
     */
    public function getAvailableLocales(): array
    {
        return $this->availableLocales;
    }

    /**
     * Verifica se um arquivo de tradução existe
     */
    public function translationFileExists(string $locale): bool
    {
        return File::exists($this->getFilePath($locale));
    }

    /**
     * Obtém o tamanho do arquivo de tradução em bytes
     */
    public function getFileSize(string $locale): int
    {
        $file = $this->getFilePath($locale);
        return File::exists($file) ? File::size($file) : 0;
    }

    /**
     * Cria backup de um arquivo de tradução
     */
    public function createBackup(string $locale): string
    {
        $source = $this->getFilePath($locale);

        if (!File::exists($source)) {
            throw new RuntimeException("Arquivo de tradução não existe para locale {$locale}");
        }

        $backupPath = $this->basePath . "/backups";

        if (!File::isDirectory($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $backupFile = "{$backupPath}/{$locale}_" . date('Y-m-d_H-i-s') . ".json";

        if (!File::copy($source, $backupFile)) {
            throw new RuntimeException("Erro ao criar backup para locale {$locale}");
        }

        return $backupFile;
    }
}
