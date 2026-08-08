<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides import export and clear-all portability controls', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-import-views"')
        ->toContain('id="settings-export-views"')
        ->toContain('id="settings-clear-views" disabled');
});

it('parses versioned or legacy objects through bounded normalisation', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('const parseImportedViews=raw=>')
        ->toContain('envelope?.version===savedViewsVersion?envelope.views:envelope')
        ->toContain('.slice(0,maxSavedViews)')
        ->toContain('.slice(0,maxSavedViewNameLength)')
        ->toContain('normaliseSavedState(state)');
});

it('supports merge or confirmed replacement and reports invalid JSON', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("window.confirm('Replace existing saved views? Cancel to merge.')")
        ->toContain('replace?incoming:{...existing,...incoming}')
        ->toContain("copyStatus.textContent='Saved views JSON is invalid'")
        ->toContain("copyStatus.textContent='Imported '");
});

it('requires confirmation before clearing all local views', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("window.confirm('Delete all saved workspace views?')")
        ->toContain('writeSavedViews({})')
        ->toContain("copyStatus.textContent='Deleted all saved workspace views'");
});

it('keeps imported state value-free', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('normaliseSavedState(state)')
        ->not->toContain('data-copy-setting-value')
        ->not->toContain('importedSettingValues');
});
