<?php
declare(strict_types=1);

it('integrates Page results and pagination without replacing shared Grid markup', function (): void {
    $root = dirname(__DIR__, 5);
    $script = (string) file_get_contents($root . '/app/zoosper-page/resources/admin/js/page-grid-search.js');
    $css = (string) file_get_contents($root . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css');
    $assets = require $root . '/app/zoosper-page/config/admin_assets.php';
    $jsVersion = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $script)), 0, 12);
    $cssVersion = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)), 0, 12);

    expect($script)->toContain("page.querySelector('.grid-table')")
        ->toContain("/^Showing\\s/i")
        ->toContain("page-grid-index__pagination")
        ->toContain("page-grid-index--enhanced")
        ->not->toContain('innerHTML')
        ->not->toContain('cloneNode')
        ->and($css)->toContain('.page-grid-index__summary')
        ->toContain('.page-grid-index__table')
        ->toContain('.page-grid-index__pagination')
        ->toContain('border-radius: 0 0 .75rem .75rem;')
        ->toContain('@media (max-width: 48rem)')
        ->and($assets['assets']['zoosper-page-grid-search-script']['path'] ?? null)
        ->toBe('/asset/zoosper-page/js/page-grid-search.js?v=' . $jsVersion)
        ->and($assets['assets']['zoosper-page-grid-workspace-style']['path'] ?? null)
        ->toBe('/asset/zoosper-page/css/page-grid-workspace.css?v=' . $cssVersion);
});

it('keeps the cohesive result surface Page-owned', function (): void {
    $root = dirname(__DIR__, 5);
    $shared = (string) file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css');

    expect($shared)->not->toContain('page-grid-index__summary')
        ->not->toContain('page-grid-index__pagination');
});
