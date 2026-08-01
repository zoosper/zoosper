<?php

declare(strict_types=1);

it('registers every shipped admin Grid runtime asset exactly once', function (): void {
    $packageRoot = dirname(__DIR__, 3);
    $manifest = require $packageRoot . '/config/admin_assets.php';

    $stylesheetPaths = array_column($manifest['stylesheets'] ?? [], 'path');
    $scriptPaths = array_column($manifest['scripts'] ?? [], 'path');

    expect($stylesheetPaths)->toBe([
        'resources/admin/css/grid-workspace.css',
        'resources/admin/css/grid-workspace-status.css',
        'resources/admin/css/grid-workspace-view-actions.css',
        'resources/admin/css/grid-workspace-live.css',
        'resources/admin/css/grid-compact-workspace.css',
    ])->and($scriptPaths)->toBe([
        'resources/admin/js/grid-workspace.js',
        'resources/admin/js/grid-workspace-view-actions.js',
        'resources/admin/js/grid-workspace-page-size.js',
        'resources/admin/js/grid-compact-workspace.js',
    ])->and($stylesheetPaths)->toHaveCount(count(array_unique($stylesheetPaths)))
        ->and($scriptPaths)->toHaveCount(count(array_unique($scriptPaths)));

    foreach (array_merge($stylesheetPaths, $scriptPaths) as $relativePath) {
        expect($packageRoot . '/' . $relativePath)->toBeFile();
    }
});

it('keeps all admin Grid scripts deferred', function (): void {
    $packageRoot = dirname(__DIR__, 3);
    $manifest = require $packageRoot . '/config/admin_assets.php';

    foreach ($manifest['scripts'] ?? [] as $script) {
        expect($script['defer'] ?? false)->toBeTrue();
    }
});
