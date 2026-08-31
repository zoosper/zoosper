<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('uses a versioned bounded and normalised saved-view store', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('savedViewsVersion=1')
        ->toContain('maxSavedViews=25')
        ->toContain('maxSavedViewNameLength=60')
        ->toContain('const normaliseSavedState=state=>')
        ->toContain('JSON.stringify({version:savedViewsVersion,views})');
});

it('protects capacity and confirmed overwrites', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("copyStatus.textContent='Saved view limit reached'")
        ->toContain("window.confirm('Replace saved view '+name+'?')")
        ->toContain('normaliseSavedState(workspaceState())');
});

it('supports rename and value-free JSON export', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-rename-view" disabled')
        ->toContain('id="settings-export-views"')
        ->toContain("window.prompt('Rename workspace view',oldName)")
        ->toContain("copyStatus.textContent='Renamed workspace view to '")
        ->toContain("copyStatus.textContent='Copied saved views JSON'")
        ->not->toContain('data-copy-setting-value');
});










