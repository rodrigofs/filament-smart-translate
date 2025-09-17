<?php

namespace Rodrigofs\FilamentSmartTranslate\Traits\Resource;

use Rodrigofs\FilamentSmartTranslate\TranslationHelper;
use UnitEnum;

/**
 * @mixin \Filament\Resources\Resource
 *
 * @see \Filament\Resources\Resource
 */
trait ResourceTranslateble
{
    public static function getModelLabel(): string
    {
        return __(TranslationHelper::translateWithFallback(parent::getModelLabel(), 'resources'));
    }

    public static function getNavigationGroup(): UnitEnum | string | null
    {
        if (is_null(self::$navigationGroup)) {
            return null;
        }

        if (self::$navigationGroup instanceof UnitEnum) {
            return self::$navigationGroup;
        }

        return __(TranslationHelper::translateWithFallback(self::$navigationGroup, 'navigations'));
    }
}
