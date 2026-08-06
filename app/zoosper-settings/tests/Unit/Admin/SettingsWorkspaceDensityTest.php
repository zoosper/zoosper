<?php

declare(strict_types=1);

it('provides remembered comfortable and compact density modes', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');

    expect($view)->toContain('id="settings-density"')
        ->toContain('<option value="comfortable">Comfortable</option>')
        ->toContain('<option value="compact">Compact</option>')
        ->toContain("localStorage.getItem('zoosper.settings.density')")
        ->toContain("localStorage.setItem('zoosper.settings.density',density.value)")
        ->toContain('workspace.dataset.density=density.value');
});

it('resets density with the existing Reset view operation', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');

    expect($view)->toContain("density.value='comfortable'")
        ->toContain("workspace.dataset.density='comfortable'")
        ->toContain("localStorage.removeItem('zoosper.settings.density')");
});

it('provides a print view without settings mutation controls', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');

    expect($view)->toContain('id="settings-print"')
        ->toContain("printView.addEventListener('click',()=>window.print())")
        ->toContain('@media print')
        ->toContain('.settings-icon-button,.settings-actions,.settings-copy-status,.settings-edit-control{display:none!important}')
        ->toContain('.settings-print-header{display:flex!important;');
});
