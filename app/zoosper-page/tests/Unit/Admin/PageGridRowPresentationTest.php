<?php

declare(strict_types=1);

it('enhances Page rows without changing persisted Grid column keys', function (): void {
    $root = dirname(__DIR__, 5);
    $script = (string) file_get_contents($root . '/app/zoosper-page/resources/admin/js/page-grid-search.js');
    $css = (string) file_get_contents($root . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css');
    $assets = require $root . '/app/zoosper-page/config/admin_assets.php';
    $jsVersion = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $script)), 0, 12);
    $cssVersion = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)), 0, 12);

    expect($script)
        ->toContain('th[data-grid-column="title"]')
        ->toContain('th[data-grid-column="slug"]')
        ->toContain('td[data-grid-column="status"]')
        ->toContain('td[data-grid-column="site_name"]')
        ->toContain('td[data-grid-column="actions"]')
        ->toContain('titleSort.textContent = `Title & slug${sortMarker}`')
        ->toContain('slug.textContent = `/${slugCell.textContent?.trim() ?? \'\'}`')
        ->toContain('page-grid-index__status--${status}')
        ->toContain("action.classList.add('page-grid-index__row-action')")
        ->not->toContain('innerHTML')
        ->not->toContain('cloneNode')
        ->and($css)
        ->toContain('Phase 12B-C4: Page-owned identity, status, Site and row-action presentation.')
        ->toContain('.page-grid-index__status--published')
        ->toContain('.page-grid-index__status--draft')
        ->toContain('.page-grid-index__status--archived')
        ->toContain('.page-grid-index__site-dot')
        ->toContain('.page-grid-index__row-action')
        ->and($assets['assets']['zoosper-page-grid-search-script']['path'] ?? null)
        ->toBe('/asset/zoosper-page/js/page-grid-search.js?v=' . $jsVersion)
        ->and($assets['assets']['zoosper-page-grid-workspace-style']['path'] ?? null)
        ->toBe('/asset/zoosper-page/css/page-grid-workspace.css?v=' . $cssVersion);
});

it('keeps Page row presentation outside the shared Grid package', function (): void {
    $root = dirname(__DIR__, 5);
    $shared = (string) file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css');

    expect($shared)
        ->not->toContain('page-grid-index__status')
        ->not->toContain('page-grid-index__row-action');
});
