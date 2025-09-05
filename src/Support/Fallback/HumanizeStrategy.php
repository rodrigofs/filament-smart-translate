<?php

namespace Rodrigofs\FilamentSmartTranslate\Support\Fallback;

use Illuminate\Support\Str;

final readonly class HumanizeStrategy implements FallbackStrategyInterface
{
    public function apply(string $key): string
    {
        return Str::title(Str::snake($key, ' '));
    }
}
