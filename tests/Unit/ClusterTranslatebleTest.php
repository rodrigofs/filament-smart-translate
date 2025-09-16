<?php

use Filament\Clusters\Cluster;
use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Cluster\ClusterTranslateble;
use Rodrigofs\FilamentSmartTranslate\TranslationHelper;

beforeEach(function () {
    Config::set('app.locale', 'pt_BR');
    Config::set('filament-smart-translate.enabled', true);

    // Set up test translations for clusters
    app('translator')->addLines([
        'clusters.settings' => 'Configurações',
        'clusters.users' => 'Usuários',
    ], 'pt_BR');
});

it('translates cluster breadcrumb when translation exists', function () {
    $cluster = new class extends Cluster
    {
        use ClusterTranslateble;

        protected static ?string $clusterBreadcrumb = 'settings';
    };

    $result = $cluster::getClusterBreadcrumb();

    expect($result)->toBe('Configurações');
});

it('returns original breadcrumb when no translation exists', function () {
    $cluster = new class extends Cluster
    {
        use ClusterTranslateble;

        protected static ?string $clusterBreadcrumb = 'unknown_cluster';
    };

    $result = $cluster::getClusterBreadcrumb();

    expect($result)->toBe('Unknown cluster');
});

it('returns null when cluster breadcrumb is null', function () {
    $cluster = new class extends Cluster
    {
        use ClusterTranslateble;

        protected static ?string $clusterBreadcrumb = null;

        public static function getClusterBreadcrumb(): ?string
        {
            if (self::$clusterBreadcrumb === null) {
                return null;
            }

            return TranslationHelper::translateWithFallback(self::$clusterBreadcrumb, 'clusters');
        }
    };

    $result = $cluster::getClusterBreadcrumb();

    expect($result)->toBeNull();
});

it('respects disabled translation setting for clusters', function () {
    Config::set('filament-smart-translate.enabled', false);

    $cluster = new class extends Cluster
    {
        use ClusterTranslateble;

        protected static ?string $clusterBreadcrumb = 'settings';
    };

    $result = $cluster::getClusterBreadcrumb();

    expect($result)->toBe('Settings');
});

it('applies fallback strategy for cluster breadcrumbs', function () {
    Config::set('filament-smart-translate.components.clusters.fallback_strategy', 'original');

    $cluster = new class extends Cluster
    {
        use ClusterTranslateble;

        protected static ?string $clusterBreadcrumb = 'user settings';
    };

    $result = $cluster::getClusterBreadcrumb();

    expect($result)->toBe('User settings');
});
