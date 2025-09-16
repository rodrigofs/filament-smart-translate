<?php

namespace Rodrigofs\FilamentSmartTranslate\Support\Overrides;

use Filament\Infolists\Components\Entry;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

class EntryWrapper extends Entry
{
    public function getLabel(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        if (filled($label = $this->getBaseLabel())) {
            return $label;
        }

        return TranslationHelper::translateWithFallback($this->getName(), 'entries');
    }
}
