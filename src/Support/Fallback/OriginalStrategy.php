<?php

namespace Rodrigofs\FilamentAutoTranslate\Support\Fallback;

final readonly class OriginalStrategy implements FallbackStrategyInterface
{
    public function apply(string $key): string
    {
        return $key;
    }
}
