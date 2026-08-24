<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit\Asset;

it('registers the package-owned polish layer after established Grid assets', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $asset = $manifest['assets']['zoosper-admin-grid-polish-style'] ?? null;
    $stylesheet = $root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css';
    $version = substr((string) hash_file('sha256', $stylesheet), 0, 12);

    expect($asset)->toBeArray()
        ->and($asset['type'] ?? null)->toBe('style')
        ->and($asset['path'] ?? '')->toBe('/asset/zoosper-admin-grid/css/grid-admin-polish.css?v=' . $version)
        ->and($asset['sort_order'] ?? null)->toBe(95);
});

it('integrates semantic themes responsive layout focus and reduced motion', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css',
    );

    expect($css)->toContain('var(--admin-surface')
        ->toContain('var(--admin-text')
        ->toContain(':root[data-admin-theme="dark"]')
        ->toContain('@media (max-width: 48rem)')
        ->toContain('@media (max-width: 24.375rem)')
        ->toContain('.grid-compact-toolbar {')
        ->toContain('.grid-compact-display-tools,')
        ->toContain(':focus-visible')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->not->toContain('<style')
        ->not->toContain('javascript:');
});

it('keeps wide rows traceable across themes and interaction states', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css',
    );

    expect($css)->toContain(':where([data-grid-workspace], .grid-table) {')
        ->toContain('--grid-row-even: var(--grid-surface-subtle)')
        ->toContain('--grid-row-separator:')
        ->toContain('--grid-header-separator:')
        ->toContain('--grid-column-separator:')
        ->toContain('border-right: 1px solid var(--grid-column-separator)')
        ->toContain('.grid-table th:last-child,')
        ->toContain('border-bottom: 2px solid var(--grid-header-separator)')
        ->toContain('border-bottom: 1px solid var(--grid-row-separator)')
        ->toContain('.grid-table tbody tr:nth-child(even) > td')
        ->toContain('.grid-table tbody tr:hover > td')
        ->toContain('.grid-table tbody tr:focus-within')
        ->toContain('.grid-table.grid-has-selection tbody tr.is-selected > td')
        ->toContain('.grid-table.grid-has-selection tbody tr.is-selected > td:first-child')
        ->toContain('--grid-row-selected-hover:')
        ->toContain(':root[data-admin-theme="dark"] :where([data-grid-workspace], .grid-table)')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

it('publishes explicit accessible relationships for compact controls', function (): void {
    $root = dirname(__DIR__, 5);
    $toolbar = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridCompactToolbarRenderer.php',
    );
    $workspace = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridCompactWorkspaceRenderer.php',
    );

    expect($toolbar)->toContain('aria-controls="grid-filters-panel"')
        ->toContain('aria-controls="grid-columns-panel"')
        ->toContain('aria-label="Rows per page"')
        ->and($workspace)->toContain('id="grid-filters-panel"')
        ->toContain('id="grid-columns-panel"')
        ->toContain('aria-label="Close filters"')
        ->toContain('aria-label="Close columns"')
        ->not->toContain('onclick=')
        ->not->toContain('<style')
        ->not->toContain('<script');
});

it('preserves Grid mutation security boundaries while changing presentation', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridWorkspaceMutationFormsRenderer.php',
    );

    expect($source)->toContain('method="post"')
        ->toContain('Grid workspace mutation forms require a CSRF field and token.')
        ->toContain('Grid workspace form action must use an application-local path.');
});
