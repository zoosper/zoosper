<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit\Asset;

it('registers every shipped admin Grid runtime asset exactly once', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';

    $stylesheetPaths = array_column($manifest['stylesheets'] ?? [], 'path');
    $scriptPaths = array_column($manifest['scripts'] ?? [], 'path');

    expect($stylesheetPaths)->toBe([
        'resources/admin/css/grid-workspace.css',
        'resources/admin/css/grid-workspace-status.css',
        'resources/admin/css/grid-workspace-view-actions.css',
        'resources/admin/css/grid-workspace-live.css',
        'resources/admin/css/grid-compact-workspace.css',
        'resources/admin/css/grid-compact-column-order.css',
    ])->and($scriptPaths)->toBe([
        'resources/admin/js/grid-workspace.js',
        'resources/admin/js/grid-workspace-view-actions.js',
        'resources/admin/js/grid-workspace-page-size.js',
        'resources/admin/js/grid-compact-workspace.js',
        'resources/admin/js/grid-compact-column-order.js',
    ])->and(count($stylesheetPaths))->toBe(count(array_unique($stylesheetPaths)))
        ->and(count($scriptPaths))->toBe(count(array_unique($scriptPaths)));
});

it('keeps all admin Grid scripts deferred', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';

    foreach ($manifest['scripts'] ?? [] as $script) {
        expect($script['defer'] ?? false)->toBeTrue();
    }
});
