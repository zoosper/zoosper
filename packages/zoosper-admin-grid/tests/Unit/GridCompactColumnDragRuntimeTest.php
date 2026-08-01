<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

it('registers the compact column-order asset after the compact workspace asset', function (): void {
    $root = dirname(__DIR__, 4);
    $assets = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $scripts = array_column($assets['scripts'], 'path');

    expect($scripts)->toContain('resources/admin/js/grid-compact-workspace.js')
        ->toContain('resources/admin/js/grid-compact-column-order.js')
        ->and(array_search('resources/admin/js/grid-compact-column-order.js', $scripts, true))
        ->toBeGreaterThan(array_search('resources/admin/js/grid-compact-workspace.js', $scripts, true));
});

it('binds drag ordering to compact column markup without enabling locked columns', function (): void {
    $root = dirname(__DIR__, 4);
    $source = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-column-order.js',
    );

    expect($source)->toContain('.grid-compact-column[data-column-key]')
        ->toContain('[draggable="true"]')
        ->toContain("addEventListener('dragstart'")
        ->toContain("addEventListener('dragover'")
        ->toContain("addEventListener('drop'")
        ->toContain('input[name="column_order[]"]')
        ->not->toContain('innerHTML')
        ->not->toContain('eval(');
});
