<?php

declare(strict_types=1);

it('renders an explicit accessible popup close control', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridWorkspaceMutationFormsRenderer.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('data-grid-settings-close')
        ->and($source)->toContain('aria-label="Close saved-view management"')
        ->and($source)->toContain('Manage saved views');
});

it('anchors the popup inside its Grid workspace and preserves closure', function (): void {
    $root = dirname(__DIR__, 4);
    $script = file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-workspace-command-bar.js',
    );

    expect($script)->not->toBeFalse()
        ->and($script)->toContain('getBoundingClientRect()')
        ->and($script)->toContain("closest('[data-grid-workspace]')")
        ->and($script)->toContain("close?.addEventListener('click'")
        ->and($script)->toContain("e.key==='Escape'")
        ->and($script)->toContain('toggle?.focus()');
});

it('publishes the current workspace-bound popup asset', function (): void {
    $root = dirname(__DIR__, 4);
    $assets = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';

    expect($assets['assets'])->toHaveKey('zoosper-admin-grid-command-bar-popup-style')
        ->and($assets['assets']['zoosper-admin-grid-command-bar-script']['path'])
        ->toBe('/asset/zoosper-admin-grid/js/grid-workspace-command-bar.js?v=7g1');
});











