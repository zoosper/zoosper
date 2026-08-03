<?php

declare(strict_types=1);

it('keeps saved-view management out of normal page flow', function (): void {
    $root=dirname(__DIR__,4);
    $toolbar=file_get_contents($root.'/packages/zoosper-admin-grid/src/GridCompactToolbarRenderer.php');
    $forms=file_get_contents($root.'/packages/zoosper-admin-grid/src/GridWorkspaceMutationFormsRenderer.php');
    expect($toolbar)->not->toBeFalse()->and($forms)->not->toBeFalse()
        ->and($toolbar)->toContain('data-grid-settings-toggle')
        ->and($toolbar)->toContain('title="Manage saved views"')
        ->and($forms)->toContain('id="grid-workspace-settings"')
        ->and($forms)->toContain('data-grid-settings hidden');
});

it('enforces one command panel at a time and escape closure', function (): void {
    $root=dirname(__DIR__,4);
    $script=file_get_contents($root.'/packages/zoosper-admin-grid/resources/admin/js/grid-workspace-command-bar.js');
    expect($script)->not->toBeFalse()
        ->and($script)->toContain('closePanels')
        ->and($script)->toContain("event.key !== 'Escape'")
        ->and($script)->toContain('settings.hidden = true');
});

it('publishes command bar assets', function (): void {
    $root=dirname(__DIR__,4);$a=require $root.'/packages/zoosper-admin-grid/config/admin_assets.php';
    expect($a['assets'])->toHaveKeys(['zoosper-admin-grid-command-bar-style','zoosper-admin-grid-command-bar-script']);
});
