<?php

namespace Rodrigofs\FilamentSmartTranslate\Support;

use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use Filament\Tables\Columns\Column;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

readonly class OverrideWrapper
{
    public function __construct(
        private Field | Entry | Column $field,
        private string $context = 'fields'
    ) {}

    public function getLabel(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        if (method_exists($this->field, 'getBaseLabel')) {
            if (filled($label = $this->field->getBaseLabel())) {
                return $label;
            }
        }

        return TranslationHelper::translateWithFallback($this->field->getName(), $this->context);
    }
}
