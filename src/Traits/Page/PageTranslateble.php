<?php

namespace Rodrigofs\FilamentSmartTranslate\Traits\Page;

use Rodrigofs\FilamentSmartTranslate\TranslationHelper;
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

        return __(TranslationHelper::translateWithFallback(self::$navigationGroup, 'navigations'));
    }

    public function getModelLabel(): ?string
    {
        $parentLabel = parent::getModelLabel() ?? null;

        if ($parentLabel === null) {
            return null;
        }

        return __(TranslationHelper::translateWithFallback($parentLabel, 'pages'));
    }

    public static function getNavigationLabel(): string
    {
        $navigationLabel = parent::getNavigationLabel();

        return __(TranslationHelper::translateWithFallback($navigationLabel, 'pages'));
    }

    public function getTitle(): string
    {
        $title = parent::getTitle();

        return __(TranslationHelper::translateWithFallback($title, 'pages'));
    }
}
