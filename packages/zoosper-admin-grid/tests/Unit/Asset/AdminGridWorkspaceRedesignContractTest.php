<?php
declare(strict_types=1);

it('owns the shared cardless workspace redesign without feature-specific selectors', function (): void {
    $root = dirname(__DIR__, 5);
    $path = $root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css';
    $css = (string) file_get_contents($path);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $version = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)), 0, 12);

    expect($css)->toContain('Phase 12B-A: shared cardless Admin Grid workspace shell.')
        ->toContain('[data-grid-workspace]')
        ->toContain('.grid-compact-toolbar')
        ->toContain('.grid-filter-chips:not(:empty)')
        ->toContain('.grid-workspace__navigation')
        ->toContain('@media (max-width: 48rem)')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->not->toContain('admin.pages')
        ->not->toContain('/admin/pages')
        ->and($manifest['assets']['zoosper-admin-grid-polish-style']['path'] ?? null)
        ->toBe('/asset/zoosper-admin-grid/css/grid-admin-polish.css?v=' . $version);
});
