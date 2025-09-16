<?php

namespace Rodrigofs\FilamentSmartTranslate\Cluster;

use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

/**
 * @mixin \Filament\Clusters\Cluster
 *
 * @see \Filament\Clusters\Cluster
 */
trait ClusterTranslateble
{
    public static function getClusterBreadcrumb(): ?string
    {
        return __(TranslationHelper::translateWithFallback(parent::getClusterBreadcrumb(), 'clusters'));
    }
}
