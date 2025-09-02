<?php

namespace Rodrigofs\FilamentAutoTranslate\Cluster;

use Rodrigofs\FilamentAutoTranslate\TranslationHelper;

/**
 * @mixin \Filament\Clusters\Cluster
 *
 * @see \Filament\Clusters\Cluster
 */
trait ClusterTranslateble
{
    public static function getClusterBreadcrumb(): ?string
    {
        return TranslationHelper::translateWithFallback(parent::getClusterBreadcrumb(), 'clusters');
    }
}
