<?php

declare(strict_types=1);

it('owns the Audit Log collection header and canonical search presentation', function (): void {
    $root = dirname(__DIR__, 3);

    $view = (string) file_get_contents(
        $root . '/resources/views/audit-log/index.php',
    );

    $css = (string) file_get_contents(
        $root . '/resources/admin/css/audit-log-workspace.css',
    );

    $script = (string) file_get_contents(
        $root . '/resources/admin/js/audit-log-workspace.js',
    );

    $assets = require $root . '/config/admin_assets.php';
    $roots = require $root . '/config/assets.php';

    expect($view)
        ->toContain('audit-log-index')
        ->toContain('System / Audit Log')
        ->toContain('Review administrative activity across Zoosper.')
        ->toContain('$workspaceHtml')
        ->toContain('$gridHtml')
        ->and($css)
        ->toContain('Phase 12C-B1')
        ->toContain('.audit-log-search')
        ->and($script)
        ->toContain("filterForm?.querySelector('[name=\"q\"]')")
        ->toContain("query.setAttribute('form', filterForm.id)")
        ->toContain("query.placeholder = 'Search action, actor or summary'")
        ->toContain('filterForm.requestSubmit()')
        ->not->toContain('localStorage')
        ->not->toContain('fetch(')
        ->and($roots['zoosper-audit'] ?? null)
        ->toBe($root . '/resources/admin')
        ->and($assets['assets']['zoosper-audit-log-workspace-script']['attributes']['defer'] ?? false)
        ->toBeTrue();
});

it('keeps Audit presentation outside Pages and the shared Grid package', function (): void {
    $repo = dirname(__DIR__, 5);

    $pageCss = (string) file_get_contents(
        $repo . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css',
    );

    $sharedCss = (string) file_get_contents(
        $repo . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css',
    );

    expect($pageCss)
        ->not->toContain('.audit-log-index')
        ->and($sharedCss)
        ->not->toContain('.audit-log-index')
        ->not->toContain('Search action, actor or summary');
});
