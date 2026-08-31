<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Asset;

test('compact Grid behaviour uses one package-owned secured runtime', function (): void {
    $root = dirname(__DIR__, 5);
    $adminManifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $gridManifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $adminAssets = $adminManifest['assets'] ?? [];
    $gridAssets = $gridManifest['assets'] ?? [];

    expect($adminAssets)->not->toHaveKey('zoosper-grid-compact-script')
        ->and($gridAssets['zoosper-admin-grid-compact-workspace-script']['path'] ?? null)
        ->toStartWith('/asset/zoosper-admin-grid/js/grid-compact-workspace.js?v=')
        ->and($adminAssets['zoosper-grid-columns-script']['path'] ?? null)
        ->toStartWith('/asset/zoosper-admin/js/zoosper-grid-columns.js?v=')
        ->and(is_file($root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-workspace.js'))
        ->toBeTrue()
        ->and(is_file($root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-columns.js'))
        ->toBeTrue();

    $definitions = require $root . '/app/zoosper-admin/config/assets.php';
    expect($definitions['assets'] ?? null)->toBe('resources/assets');
});










