<?php

namespace Rodrigofs\FilamentAutoTranslate\Support\Fallback;

use Stringable;

final readonly class TitleCaseStrategy implements Stringable
{
    public function __construct(
        protected string $key,
    ) {}

    public function __toString()
    {
        return ucwords($this->key);
    }
}
