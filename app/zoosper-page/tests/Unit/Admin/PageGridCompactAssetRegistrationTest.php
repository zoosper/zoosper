<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('compact Page Grid layout consumes the package-owned Admin Grid asset', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $stylesheets = $manifest['stylesheets'] ?? [];
    $compactAsset = array_values(array_filter(
        $stylesheets,
        static fn (mixed $asset): bool => is_array($asset)
            && ($asset['path'] ?? null) === 'resources/admin/css/grid-compact-workspace.css',
    ));
    $gridCssPath = $root . '/packages/zoosper-admin-grid/resources/admin/css/grid-compact-workspace.css';
    $gridCss = (string) file_get_contents($gridCssPath);

    expect($compactAsset)->toHaveCount(1)
        ->and($compactAsset[0]['priority'] ?? null)->toBe(80)
        ->and(is_file($gridCssPath))->toBeTrue()
        ->and($gridCss)->toContain('[data-grid-workspace]')
        ->toContain('.grid-compact-filters')
        ->toContain('.grid-compact-state')
        ->toContain('.grid-compact-panel[hidden]');

    expect(is_file(
        $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-compact-v2.css',
    ))->toBeFalse();
});










