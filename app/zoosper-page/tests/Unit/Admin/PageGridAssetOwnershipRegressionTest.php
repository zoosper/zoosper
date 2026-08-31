<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('Page consumes package-owned compact Grid behaviour without duplicating runtime assets', function (): void {
    $root = dirname(__DIR__, 5);
    $pageManifest = (string) file_get_contents(
        $root . '/app/zoosper-page/config/admin_assets.php',
    );
    $adminManifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $gridManifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $adminAssets = $adminManifest['assets'] ?? [];
    $gridAssets = $gridManifest['assets'] ?? [];

    expect($pageManifest)
        ->not->toContain('zoosper-grid-compact-script')
        ->not->toContain('zoosper-admin-grid-compact-workspace-script')
        ->not->toContain('zoosper-grid-columns-script')
        ->not->toContain('/assets/admin/js/zoosper-grid-compact.js')
        ->not->toContain('/assets/admin/js/zoosper-grid-columns.js')
        ->and($adminAssets)->not->toHaveKey('zoosper-grid-compact-script')
        ->and($gridAssets['zoosper-admin-grid-compact-workspace-script']['path'] ?? null)
        ->toStartWith('/asset/zoosper-admin-grid/js/grid-compact-workspace.js?v=')
        ->and($adminAssets['zoosper-grid-columns-script']['path'] ?? null)
        ->toStartWith('/asset/zoosper-admin/js/zoosper-grid-columns.js?v=');
});










