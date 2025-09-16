<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentSmartTranslate\Support\Fallback\Strategies;

use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyInterface;

final readonly class LowerCaseStrategy implements FallbackStrategyInterface
{
    public function apply(string $key): string
    {
        if (empty($key)) {
            return '';
        }

        return (string) str($key)
            ->afterLast('.')
            ->replace(['-', '_'], '-')
            ->lower();
    }
}
