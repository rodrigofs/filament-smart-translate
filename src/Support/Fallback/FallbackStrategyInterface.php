<?php

namespace Rodrigofs\FilamentSmartTranslate\Support\Fallback;

interface FallbackStrategyInterface
{
    public function apply(string $key): string;
}
