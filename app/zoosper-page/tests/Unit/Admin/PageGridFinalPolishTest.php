<?php

declare(strict_types=1);

it('finalises Page Grid proportions and theme-aware presentation', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents(
        $root . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css',
    );
    $assets = require $root . '/app/zoosper-page/config/admin_assets.php';
    $version = substr(
        hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)),
        0,
        12,
    );

    expect($css)
        ->toContain('Phase 12B-C5: final Page Grid proportions, themes and responsive polish.')
        ->toContain('table-layout: fixed;')
        ->toContain('[data-grid-column="id"]')
        ->toContain('[data-grid-column="title"]')
        ->toContain('[data-grid-column="status"]')
        ->toContain('[data-grid-column="site_name"]')
        ->toContain('[data-grid-column="actions"]')
        ->toContain(':root[data-admin-theme="dark"]')
        ->toContain(':root[data-admin-theme="ocean"]')
        ->toContain('@media (max-width: 48rem)')
        ->toContain('@media (prefers-contrast: more)')
        ->and($assets['assets']['zoosper-page-grid-workspace-style']['path'] ?? null)
        ->toBe('/asset/zoosper-page/css/page-grid-workspace.css?v=' . $version);
});

it('makes Page row enhancement idempotent', function (): void {
    $root = dirname(__DIR__, 5);
    $script = (string) file_get_contents(
        $root . '/app/zoosper-page/resources/admin/js/page-grid-search.js',
    );

    expect($script)
        ->toContain("row.dataset.pageGridRowEnhanced === 'true'")
        ->toContain("row.dataset.pageGridRowEnhanced = 'true'")
        ->and(substr_count($script, 'pageGridRowEnhanced'))
        ->toBe(2);
});

it('keeps final Page polish outside the shared Grid package', function (): void {
    $root = dirname(__DIR__, 5);
    $shared = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css',
    );

    expect($shared)
        ->not->toContain('Phase 12B-C5')
        ->not->toContain('pageGridRowEnhanced');
});
