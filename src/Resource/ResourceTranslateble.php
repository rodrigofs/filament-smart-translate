<?php

namespace Rodrigofs\FilamentAutoTranslate\Resource;

use Rodrigofs\FilamentAutoTranslate\TranslationHelper;
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
        return TranslationHelper::translateWithFallback(parent::getModelLabel(), 'resource_labels');
    }

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
}
