<?php

namespace Rodrigofs\FilamentSmartTranslate\Support\Overrides;

use Filament\Tables\Columns\Column;
use Illuminate\Contracts\Support\Htmlable;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

class ColumnWrapper extends Column
{
    public function getLabel(): string | Htmlable
    {
        return TranslationHelper::translateWithFallback($this->getName(), 'columns');
    }
}
