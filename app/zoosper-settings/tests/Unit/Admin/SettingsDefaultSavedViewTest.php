<?php

declare(strict_types=1);

it('supports a browser-local default saved view without overriding explicit URL state', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-default-view" disabled')
        ->toContain("defaultSavedViewKey='zoosper.settings.defaultSavedView'")
        ->toContain("localStorage.setItem(defaultSavedViewKey,name)")
        ->toContain("explicitWorkspaceState=['q','view','module','density'].some")
        ->toContain('if(!explicitWorkspaceState&&defaultSavedView&&readSavedViews()[defaultSavedView]&&!hasDirtySettingsForms())');
});

it('keeps the default pointer coherent across rename delete and clear all', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('localStorage.getItem(defaultSavedViewKey)===oldName')
        ->toContain('localStorage.setItem(defaultSavedViewKey,newName)')
        ->toContain('localStorage.getItem(defaultSavedViewKey)===name')
        ->toContain('localStorage.removeItem(defaultSavedViewKey)');
});
