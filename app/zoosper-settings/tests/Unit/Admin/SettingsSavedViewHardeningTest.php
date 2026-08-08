<?php

declare(strict_types=1);

it('uses a versioned bounded and normalised saved-view store', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('savedViewsVersion=1')
        ->toContain('maxSavedViews=25')
        ->toContain('maxSavedViewNameLength=60')
        ->toContain('const normaliseSavedState=state=>')
        ->toContain('JSON.stringify({version:savedViewsVersion,views})');
});

it('protects capacity and confirmed overwrites', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain("copyStatus.textContent='Saved view limit reached'")
        ->toContain("window.confirm('Replace saved view '+name+'?')")
        ->toContain('normaliseSavedState(workspaceState())');
});

it('supports rename and value-free JSON export', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-rename-view" disabled')
        ->toContain('id="settings-export-views"')
        ->toContain("window.prompt('Rename workspace view',oldName)")
        ->toContain("copyStatus.textContent='Renamed workspace view to '")
        ->toContain("copyStatus.textContent='Copied saved views JSON'")
        ->not->toContain('data-copy-setting-value');
});
