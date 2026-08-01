<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit\Asset;

it('preserves all established assets while adding compact column ordering', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';

    expect(array_column($manifest['stylesheets'] ?? [], 'path'))->toBe([
        'resources/admin/css/grid-workspace.css',
        'resources/admin/css/grid-workspace-status.css',
        'resources/admin/css/grid-workspace-view-actions.css',
        'resources/admin/css/grid-workspace-live.css',
        'resources/admin/css/grid-compact-workspace.css',
    ])->and(array_column($manifest['scripts'] ?? [], 'path'))->toBe([
        'resources/admin/js/grid-workspace.js',
        'resources/admin/js/grid-workspace-view-actions.js',
        'resources/admin/js/grid-workspace-page-size.js',
        'resources/admin/js/grid-compact-workspace.js',
        'resources/admin/js/grid-compact-column-order.js',
    ]);
});
