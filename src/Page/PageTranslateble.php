<?php

namespace Rodrigofs\FilamentAutoTranslate\Page;

use Rodrigofs\FilamentAutoTranslate\TranslationHelper;
use UnitEnum;

/**
 * @mixin \Filament\Pages\Page
 *
 * @see \Filament\Resources\Pages\Page
 */
trait PageTranslateble
{
    public static function getNavigationGroup(): UnitEnum | string | null
    {
        if (is_null(self::$navigationGroup)) {
            return null;
        }

        if (self::$navigationGroup instanceof UnitEnum) {
            return self::$navigationGroup;
        }

        return TranslationHelper::translateWithFallback(self::$navigationGroup, 'navigation_groups');
    }

    public function getModelLabel(): ?string
    {
        $parentLabel = parent::getModelLabel() ?? null;
        if ($parentLabel === null) {
            return null;
        }

        return TranslationHelper::translateWithFallback($parentLabel, 'resource_labels');
    }
}
