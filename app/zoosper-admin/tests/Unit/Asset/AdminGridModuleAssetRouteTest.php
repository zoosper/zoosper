<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Asset;

test('Grid scripts use the secured module asset route and have module-owned sources', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $assets = $manifest['assets'] ?? [];

    expect($assets['zoosper-grid-compact-script']['path'] ?? null)
        ->toStartWith('/asset/zoosper-admin/js/zoosper-grid-compact.js?v=');
    expect($assets['zoosper-grid-columns-script']['path'] ?? null)
        ->toStartWith('/asset/zoosper-admin/js/zoosper-grid-columns.js?v=');

    expect(is_file($root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-compact.js'))->toBeTrue();
    expect(is_file($root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-columns.js'))->toBeTrue();

    $definitions = require $root . '/app/zoosper-admin/config/assets.php';
    expect($definitions['assets'] ?? null)->toBe('resources/assets');
});
