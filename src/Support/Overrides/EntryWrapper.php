<?php

namespace Rodrigofs\FilamentSmartTranslate\Support\Overrides;

use Filament\Forms\Components\Field;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

class FieldWrapper extends Field
{

    public function getLabel(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        if (filled($label = $this->getBaseLabel())) {
            return $label;
        }

        return TranslationHelper::translateWithFallback($this->getName(), 'fields');
    }
}
