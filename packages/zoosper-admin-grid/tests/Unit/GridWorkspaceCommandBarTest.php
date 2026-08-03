<?php

declare(strict_types=1);

it('keeps saved-view management out of normal page flow', function (): void {
    $root = dirname(__DIR__, 4);
    $toolbar = file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridCompactToolbarRenderer.php',
    );
    $forms = file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridWorkspaceMutationFormsRenderer.php',
    );

    expect($toolbar)->not->toBeFalse()
        ->and($forms)->not->toBeFalse()
        ->and($toolbar)->toContain('data-grid-settings-toggle')
        ->and($toolbar)->toContain('title="Manage saved views"')
        ->and($forms)->toContain('id="grid-workspace-settings"')
        ->and($forms)->toContain('data-grid-settings hidden');
});

it('keeps saved-view management independently closable', function (): void {
    $root = dirname(__DIR__, 4);
    $script = file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-workspace-command-bar.js',
    );

    expect($script)->not->toBeFalse()
        ->and($script)->toContain('const dismiss=')
        ->and($script)->toContain("e.key==='Escape'")
        ->and($script)->toContain('settings.hidden=true')
        ->and($script)->not->toContain('closePanels')
        ->and($script)->not->toContain('data-grid-command-bar-bound');
});

it('publishes command bar assets', function (): void {
    $root = dirname(__DIR__, 4);
    $assets = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';

    expect($assets['assets'])->toHaveKeys([
        'zoosper-admin-grid-command-bar-style',
        'zoosper-admin-grid-command-bar-script',
    ])
        ->and($assets['assets']['zoosper-admin-grid-command-bar-script']['path'])
        ->toBe('/asset/zoosper-admin-grid/js/grid-workspace-command-bar.js?v=7g1');
});
