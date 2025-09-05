<?php

namespace Rodrigofs\FilamentSmartTranslate\Support\Fallback;

final readonly class TitleCaseStrategy implements FallbackStrategyInterface
{
    public function apply(string $key): string
    {
        return ucwords($key);
    }
}
