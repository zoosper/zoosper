<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides dedicated scope-aware print metadata and hides the admin shell', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('settings-print-header')
        ->toContain('Scope: <?= $e($scopeLabel) ?>')
        ->toContain('id="settings-print-generated"')
        ->toContain('.admin-sidebar,.admin-topbar,.admin-header,.admin-footer')
        ->toContain('@page{margin:14mm 12mm}');
});

it('expands all groups only for print and restores previous state afterwards', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("window.addEventListener('beforeprint'")
        ->toContain('printGroupState=groups.map(group=>group.open)')
        ->toContain('groups.forEach(group=>group.open=true)')
        ->toContain("window.addEventListener('afterprint'");
});

it('removes target and search outlines from print output', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('.settings-field:target,.settings-field.settings-match,.settings-field.settings-current-match{outline:none!important;background:transparent!important}')
        ->toContain('.settings-group summary:after{display:none!important}')
        ->toContain('.settings-icon-button,.settings-actions');
});
