<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentSmartTranslate\Data;

final readonly class TranslationData
{
    public function __construct(
        public string $key,
        public string $value,
        public string $locale
    ) {}

    /**
     * Create from array
     *
     * @param  array<string, string>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            value: $data['value'],
            locale: $data['locale']
        );
    }

    /**
     * Convert to array
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'locale' => $this->locale,
        ];
    }

    /**
     * Get translation length
     */
    public function getLength(): int
    {
        return mb_strlen($this->value);
    }

    /**
     * Check if translation is empty
     */
    public function isEmpty(): bool
    {
        return empty(trim($this->value));
    }

    /**
     * Check if translation is long (over 100 characters)
     */
    public function isLong(): bool
    {
        return $this->getLength() > 100;
    }

    /**
     * Get category based on key
     */
    public function getCategory(): string
    {
        $key = strtolower($this->key);

        return match (true) {
            str_contains($key, 'password') || str_contains($key, 'login') || str_contains($key, 'auth') => 'auth',
            str_contains($key, 'validation') || str_contains($key, 'must be') || str_contains($key, 'required') => 'validation',
            str_contains($key, 'error') || str_contains($key, 'forbidden') || str_contains($key, 'unauthorized') => 'error',
            str_contains($key, 'button') || str_contains($key, 'cancel') || str_contains($key, 'save') => 'ui',
            str_contains($key, 'resources.') => 'resources',
            str_contains($key, 'navigations.') => 'navigations',
            str_contains($key, 'actions.') => 'actions',
            str_contains($key, 'clusters.') => 'clusters',
            str_contains($key, 'pages.') => 'pages',
            str_contains($key, 'fields.') => 'fields',
            str_contains($key, 'schemas.') => 'schemas',
            str_contains($key, 'entries.') => 'entries',
            str_contains($key, 'columns.') => 'columns',
            default => 'general',
        };
    }
}
