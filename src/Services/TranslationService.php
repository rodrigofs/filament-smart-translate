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

        $file = $this->getFilePath($locale);

        if (!File::exists($file)) {
            return collect([]);
        }

        $content = File::get($file);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Erro ao decodificar JSON para locale {$locale}: " . json_last_error_msg());
        }

        return collect($data ?? []);
    }

    public function saveTranslations(string $locale, Collection $translations): void
    {
        $this->validateLocale($locale);
        $this->ensureDirectoryExists();

        $file = $this->getFilePath($locale);

        // Ordenar por chave para manter consistência
        $sortedTranslations = $translations->sortKeys();

        $json = json_encode(
            $sortedTranslations->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new RuntimeException("Erro ao codificar JSON para locale {$locale}");
        }

        if (!File::put($file, $json)) {
            throw new RuntimeException("Erro ao salvar arquivo de tradução para locale {$locale}");
        }
    }

    /**
     * Adiciona uma nova tradução
     */
    public function addTranslation(string $locale, string $key, string $value): Collection
    {
        $translations = $this->loadTranslations($locale);

        if ($translations->has($key)) {
            throw new InvalidArgumentException("A chave '{$key}' já existe");
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
            throw new InvalidArgumentException("A chave '{$key}' não existe");
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
            throw new InvalidArgumentException("A chave '{$key}' não existe");
        }

        $translations->forget($key);
        $this->saveTranslations($locale, $translations);

        return $translations;
    }

    /**
     * Remove múltiplas traduções
     */
    public function bulkDeleteTranslations(string $locale, array $keys): Collection
    {
        $translations = $this->loadTranslations($locale);

        foreach ($keys as $key) {
            $translations->forget($key);
        }

        $this->saveTranslations($locale, $translations);

        return $translations;
    }

    /**
     * Importa traduções de um array
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
