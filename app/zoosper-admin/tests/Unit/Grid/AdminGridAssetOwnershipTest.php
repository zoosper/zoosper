<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Grid;

test('compact Grid behaviour is package-owned while generic columns remain Admin-owned', function (): void {
    $root = dirname(__DIR__, 5);
    $adminManifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $gridManifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';

    expect($adminManifest['assets'] ?? [])->not->toHaveKey('zoosper-grid-compact-script')
        ->and($gridManifest['assets'] ?? [])->toHaveKey('zoosper-admin-grid-compact-workspace-script')
        ->and(is_file($root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-workspace.js'))
        ->toBeTrue()
        ->and(is_file($root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-columns.css'))
        ->toBeTrue()
        ->and(is_file($root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-columns.js'))
        ->toBeTrue()
        ->and(is_file($root . '/public/assets/admin/js/zoosper-grid-compact.js'))
        ->toBeFalse();
});










