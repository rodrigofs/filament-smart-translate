<?php

namespace Rodrigofs\FilamentAutoTranslate\Support\Fallback;

interface FallbackStrategyInterface
{
    public function apply(string $key): string;
}
