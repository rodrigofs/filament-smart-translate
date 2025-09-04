<?php

namespace Rodrigofs\FilamentAutoTranslate\Support\Fallback;

use Illuminate\Support\Str;
use Stringable;

final readonly class HumanizeStrategy implements Stringable
{
    public function __construct(
        private string $key
    ) {}

    public function __toString()
    {
        return Str::title(Str::snake($this->key, ' '));
    }
}
