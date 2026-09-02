<?php
declare(strict_types=1);

it('promotes the existing q filter without creating a second search-state owner', function (): void {
    $root = dirname(__DIR__, 5);
    $script = (string) file_get_contents($root . '/app/zoosper-page/resources/admin/js/page-grid-search.js');
    $css = (string) file_get_contents($root . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css');
    $assets = require $root . '/app/zoosper-page/config/admin_assets.php';
    $version = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $script)), 0, 12);

    expect($script)->toContain("querySelector('[name=\"q\"]')")
        ->toContain("query.type = 'search'")
        ->toContain("query.placeholder = 'Search pages by title or slug'")
        ->toContain("filterForm.id = 'page-grid-filter-form'")
        ->toContain("query.setAttribute('form', filterForm.id)")
        ->toContain('toolbar.prepend(search)')
        ->toContain('filterForm.requestSubmit()')
        ->toContain("pageInput.value = '1'")
        ->not->toContain('localStorage')
        ->not->toContain('sessionStorage')
        ->not->toContain('fetch(')
        ->and($css)->toContain('.page-grid-search')
        ->toContain('flex: 1 1 24rem;')
        ->toContain('@media (max-width: 48rem)')
        ->and($assets['assets']['zoosper-page-grid-search-script']['path'] ?? null)
        ->toBe('/asset/zoosper-page/js/page-grid-search.js?v=' . $version)
        ->and($assets['assets']['zoosper-page-grid-search-script']['attributes']['defer'] ?? false)
        ->toBeTrue();
});

it('keeps Page search presentation outside the shared Admin Grid package', function (): void {
    $root = dirname(__DIR__, 5);
    $sharedPhp = (string) file_get_contents($root . '/packages/zoosper-admin-grid/src/GridCompactWorkspaceRenderer.php');
    $sharedCss = (string) file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css');

    expect($sharedPhp . $sharedCss)->not->toContain('page-grid-search')
        ->not->toContain('Search pages by title or slug');
});
