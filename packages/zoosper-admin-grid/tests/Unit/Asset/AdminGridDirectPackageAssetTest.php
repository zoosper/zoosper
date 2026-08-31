<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit\Asset;

use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\AssetResolver;
use Zoosper\Core\Asset\ModuleAssetManifestLoader;

it('serves canonical column assets directly from the package module', function (): void {
    $root = dirname(__DIR__, 5);
    $assetDefinitions = require $root . '/packages/zoosper-admin-grid/config/assets.php';
    $registry = new AssetModuleRegistry();
    ModuleAssetManifestLoader::mergeDefinitions($registry, $assetDefinitions, 'zoosper-admin-grid');
    $resolver = new AssetResolver($registry);

    $script = $resolver->resolve('zoosper-admin-grid', 'js/grid-compact-column-order.js');
    $style = $resolver->resolve('zoosper-admin-grid', 'css/grid-compact-column-order.css');

    expect($script->mimeType)->toBe('text/javascript')
        ->and($style->mimeType)->toBe('text/css')
        ->and($script->absolutePath)->toBe(realpath($root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-column-order.js'))
        ->and($style->absolutePath)->toBe(realpath($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-compact-column-order.css'));
});

it('registers content-versioned package URLs in the admin asset registry shape', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $assets = $manifest['assets'] ?? [];
    $script = $assets['zoosper-admin-grid-column-order-script']['path'] ?? '';
    $style = $assets['zoosper-admin-grid-column-order-style']['path'] ?? '';
    $scriptHash = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", (string) file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-column-order.js'))), 0, 12);
    $styleHash = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", (string) file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-compact-column-order.css'))), 0, 12);

    expect($script)->toBe('/asset/zoosper-admin-grid/js/grid-compact-column-order.js?v=' . $scriptHash)
        ->and($style)->toBe('/asset/zoosper-admin-grid/css/grid-compact-column-order.css?v=' . $styleHash)
        ->and($assets['zoosper-admin-grid-column-order-script']['attributes']['defer'] ?? false)->toBeTrue();
});

it('retires the application compatibility bridge and registrations', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $source = var_export($manifest, true);

    expect($source)->not->toContain('zoosper-grid-column-drag')
        ->and($root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-column-drag.js')->not->toBeFile()
        ->and($root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-column-drag.css')->not->toBeFile();
});











