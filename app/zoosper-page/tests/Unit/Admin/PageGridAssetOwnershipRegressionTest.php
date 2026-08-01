<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('Page does not duplicate Admin-owned Grid runtime assets', function (): void {
    $root = dirname(__DIR__, 5);
    $pageManifest = (string) file_get_contents(
        $root . '/app/zoosper-page/config/admin_assets.php',
    );
    $adminManifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $adminAssets = $adminManifest['assets'] ?? [];

    expect($pageManifest)
        ->not->toContain('zoosper-grid-compact-script')
        ->not->toContain('zoosper-grid-columns-script')
        ->not->toContain('/assets/admin/js/zoosper-grid-compact.js')
        ->not->toContain('/assets/admin/js/zoosper-grid-columns.js');

    expect($adminAssets['zoosper-grid-compact-script']['path'] ?? null)
        ->toStartWith('/asset/zoosper-admin/js/zoosper-grid-compact.js?v=');
    expect($adminAssets['zoosper-grid-columns-script']['path'] ?? null)
        ->toStartWith('/asset/zoosper-admin/js/zoosper-grid-columns.js?v=');
});
