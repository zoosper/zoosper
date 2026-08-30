<?php

declare(strict_types=1);

it('leaves compact Grid presentation to the Admin Grid package', function (): void {
    $root = dirname(__DIR__, 5);
    $stylesheet = $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid.css';
    $css = (string) file_get_contents($stylesheet);
    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $asset = $manifest['assets']['zoosper-admin-grid-style'] ?? null;
    $version = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)), 0, 12);

    expect($css)->toContain('Compact Grid presentation is package-owned by zoosper/admin-grid.')
        ->toContain('BEGIN GRID COLUMN FILTER VISIBILITY')
        ->toContain('[data-grid-filter-columns=')
        ->not->toContain('BEGIN ZOOSPER COMPACT GRID V2')
        ->not->toContain('.grid-compact-workspace')
        ->not->toContain('.grid-compact-toolbar')
        ->not->toContain('margin: -42px')
        ->and($asset)->toBeArray()
        ->and($asset['type'] ?? null)->toBe('style')
        ->and($asset['path'] ?? null)->toBe('/asset/zoosper-admin/css/zoosper-grid.css?v=' . $version)
        ->and($asset['sort_order'] ?? null)->toBe(35);
});
