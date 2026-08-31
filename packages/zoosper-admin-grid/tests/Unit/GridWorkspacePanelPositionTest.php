<?php

declare(strict_types=1);

it('centres Filters and Columns on their own triggers inside the workspace', function (): void {
    $root = dirname(__DIR__, 4);
    $script = file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/js/grid-workspace-panel-position.js');
    expect($script)->not->toBeFalse()
        ->and($script)->toContain("trigger.closest('[data-grid-workspace]')")
        ->and($script)->toContain('triggerBox.left + (triggerBox.width / 2) - (width / 2)')
        ->and($script)->toContain("trigger.dataset.gridToggle === 'filters' ? 760 : 420")
        ->and($script)->toContain('requestAnimationFrame')
        ->and($script)->not->toContain('setTimeout');
});

it('publishes the shared panel-position assets', function (): void {
    $root = dirname(__DIR__, 4);
    $assets = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    expect($assets['assets'])->toHaveKeys([
        'zoosper-admin-grid-panel-position-style',
        'zoosper-admin-grid-panel-position-script',
    ]);
});











