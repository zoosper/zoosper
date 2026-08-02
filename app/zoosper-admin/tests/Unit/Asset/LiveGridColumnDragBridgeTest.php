<?php

declare(strict_types=1);

it('registers the live Grid drag bridge with absolute module asset URLs', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $source = var_export($manifest, true);

    expect($source)->toContain('/asset/zoosper-admin/js/zoosper-grid-column-drag.js')
        ->toContain('/asset/zoosper-admin/css/zoosper-grid-column-drag.css')
        ->not->toContain('resources/admin/js/zoosper-grid-column-drag.js')
        ->not->toContain('resources/admin/css/zoosper-grid-column-drag.css');
});

it('stores bridge files under the module asset registry root', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-column-drag.js')->toBeFile()
        ->and($root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-column-drag.css')->toBeFile()
        ->and($root . '/app/zoosper-admin/resources/admin/js/zoosper-grid-column-drag.js')->not->toBeFile()
        ->and($root . '/app/zoosper-admin/resources/admin/css/zoosper-grid-column-drag.css')->not->toBeFile();
});

it('binds the bridge to the exact live Pages column markup', function (): void {
    $root = dirname(__DIR__, 5);
    $js = (string) file_get_contents(
        $root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-column-drag.js',
    );

    expect($js)->toContain("'[data-grid-column-list]'")
        ->toContain("'.grid-compact-column[data-column-key]'")
        ->toContain("new Set(['id', 'actions'])")
        ->toContain('item.draggable = movable')
        ->toContain("addEventListener('drop'");
});
