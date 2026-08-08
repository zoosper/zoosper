<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides saved workspace view controls inside Share and output', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-saved-view"')
        ->toContain('id="settings-save-view"')
        ->toContain('id="settings-delete-view" disabled')
        ->toContain('Saved views');
});

it('persists only the allowlisted value-free workspace state', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("const savedViewsKey='zoosper.settings.savedViews'")
        ->toContain('views[name]=normaliseSavedState(workspaceState())')
        ->toContain('JSON.stringify({version:savedViewsVersion,views})')
        ->toContain('views[name]=normaliseSavedState(workspaceState())')
        ->toContain('q:input.value.trim(),view:sourceFilter.value,module:moduleFilter.value,density:density.value')
        ->not->toContain('views[name]=new FormData(form)')
        ->not->toContain('data-copy-setting-value');
});

it('validates restored select values against available options', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('[...sourceFilter.options].some(option=>option.value===state.view)')
        ->toContain('[...moduleFilter.options].some(option=>option.value===state.module)')
        ->toContain('[...density.options].some(option=>option.value===state.density)');
});

it('supports save apply and delete lifecycle with accessible status feedback', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("window.prompt('Name this workspace view')")
        ->toContain("copyStatus.textContent='Saved workspace view '")
        ->toContain("copyStatus.textContent='Applied saved view '")
        ->toContain("copyStatus.textContent='Deleted workspace view '");
});
