<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit\Asset;

it('registers the package-owned polish layer after established Grid assets', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $asset = $manifest['assets']['zoosper-admin-grid-polish-style'] ?? null;

    expect($asset)->toBeArray()
        ->and($asset['type'] ?? null)->toBe('style')
        ->and($asset['path'] ?? '')->toStartWith('/asset/zoosper-admin-grid/css/grid-admin-polish.css?v=')
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
        ->toContain(':focus-visible')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->not->toContain('<style')
        ->not->toContain('javascript:');
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
