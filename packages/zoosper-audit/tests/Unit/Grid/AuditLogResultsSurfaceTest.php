<?php

declare(strict_types=1);

it('composes existing Audit result nodes into one responsive surface', function (): void {
    $root = dirname(__DIR__, 3);
    $script = (string) file_get_contents($root . '/resources/admin/js/audit-log-workspace.js');
    $css = (string) file_get_contents($root . '/resources/admin/css/audit-log-workspace.css');
    $assets = require $root . '/config/admin_assets.php';
    $jsVersion = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $script)), 0, 12);
    $cssVersion = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)), 0, 12);

    expect($script)
        ->toContain("page.querySelector('.grid-table')")
        ->toContain("page.querySelector('.grid-pagination-controls')")
        ->toContain("footer.dataset.auditLogPagination = ''")
        ->toContain("table.insertAdjacentElement('afterend', footer)")
        ->toContain('legacyNavigation?.remove()')
        ->toContain('Node.DOCUMENT_POSITION_FOLLOWING')
        ->not->toContain('.admin-topbar__title')
        ->not->toContain('innerHTML')
        ->not->toContain('cloneNode')
        ->and($css)
        ->toContain('Phase 12C-B2: cohesive Audit result surface and compact pagination.')
        ->toContain('.audit-log-index__summary')
        ->toContain('.audit-log-index__table')
        ->toContain('.audit-log-index__pagination')
        ->toContain('grid-template-columns: minmax(7rem, 1fr) auto minmax(7rem, 1fr);')
        ->and($assets['assets']['zoosper-audit-log-workspace-script']['path'] ?? null)
        ->toBe('/asset/zoosper-audit/js/audit-log-workspace.js?v=' . $jsVersion)
        ->and($assets['assets']['zoosper-audit-log-workspace-style']['path'] ?? null)
        ->toBe('/asset/zoosper-audit/css/audit-log-workspace.css?v=' . $cssVersion);
});

it('keeps cohesive Audit presentation out of Pages and shared Grid', function (): void {
    $repo = dirname(__DIR__, 5);
    $page = (string) file_get_contents($repo . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css');
    $shared = (string) file_get_contents($repo . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css');

    expect($page)->not->toContain('audit-log-index__pagination')
        ->and($shared)->not->toContain('audit-log-index__pagination');
});
