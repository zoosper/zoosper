<?php

declare(strict_types=1);

it('adds neutral Audit row presentation without changing data semantics', function (): void {
    $root = dirname(__DIR__, 3);
    $script = (string) file_get_contents($root . '/resources/admin/js/audit-log-workspace.js');
    $css = (string) file_get_contents($root . '/resources/admin/css/audit-log-workspace.css');
    $assets = require $root . '/config/admin_assets.php';
    $jsVersion = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $script)), 0, 12);
    $cssVersion = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)), 0, 12);

    expect($script)
        ->toContain("row.dataset.auditLogRowEnhanced === 'true'")
        ->toContain("row.dataset.auditLogRowEnhanced = 'true'")
        ->toContain('td[data-grid-column="actor_email"]')
        ->toContain('td[data-grid-column="action"]')
        ->toContain('td[data-grid-column="entity_type"]')
        ->toContain('td[data-grid-column="summary"]')
        ->toContain('cell.title = value')
        ->not->toContain('innerHTML')
        ->not->toContain('textContent = `')
        ->and($css)
        ->toContain('Phase 12C-B3: Audit-owned column proportions and neutral row semantics.')
        ->toContain('[data-grid-column="created_at"]')
        ->toContain('[data-grid-column="entity_id"]')
        ->toContain('.audit-log-index__action code')
        ->toContain(':root[data-admin-theme="dark"]')
        ->toContain(':root[data-admin-theme="ocean"]')
        ->toContain('@media (prefers-contrast: more)')
        ->and($assets['assets']['zoosper-audit-log-workspace-script']['path'] ?? null)
        ->toBe('/asset/zoosper-audit/js/audit-log-workspace.js?v=' . $jsVersion)
        ->and($assets['assets']['zoosper-audit-log-workspace-style']['path'] ?? null)
        ->toBe('/asset/zoosper-audit/css/audit-log-workspace.css?v=' . $cssVersion);
});

it('does not introduce semantic colour mappings for open-ended Audit values', function (): void {
    $root = dirname(__DIR__, 3);
    $css = (string) file_get_contents($root . '/resources/admin/css/audit-log-workspace.css');

    expect($css)
        ->not->toContain('media.archived')
        ->not->toContain('page.updated')
        ->not->toContain('announcement.created');
});

it('keeps Audit row presentation out of Pages and shared Grid', function (): void {
    $repo = dirname(__DIR__, 5);
    $page = (string) file_get_contents($repo . '/app/zoosper-page/resources/admin/css/page-grid-workspace.css');
    $shared = (string) file_get_contents($repo . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css');

    expect($page)->not->toContain('audit-log-index__action')
        ->and($shared)->not->toContain('audit-log-index__action');
});
