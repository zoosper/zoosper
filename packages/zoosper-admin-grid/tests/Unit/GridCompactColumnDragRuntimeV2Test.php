<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

it('enables compact configurable columns at runtime and locks boundary keys', function (): void {
    $root = dirname(__DIR__, 4);
    $source = (string) file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-column-order.js');
    expect($source)->toContain("new Set(['id', 'actions'])")
        ->toContain('item.draggable = movable')
        ->toContain("addEventListener('dragstart'")
        ->toContain("addEventListener('drop'")
        ->toContain("document.createElement('input')")
        ->toContain("input.name = 'column_order[]'")
        ->not->toContain('innerHTML');
});

it('registers visible drag affordance styling without replacing established assets', function (): void {
    $root = dirname(__DIR__, 4);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    expect(array_column($manifest['stylesheets'], 'path'))
        ->toContain('resources/admin/css/grid-compact-column-order.css')
        ->toContain('resources/admin/css/grid-workspace-live.css');
});
