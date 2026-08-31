<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('duplicates a selected view through bounded normalisation', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-duplicate-view" disabled')
        ->toContain("window.prompt('Duplicate workspace view as',sourceName+' copy')")
        ->toContain('views[name]=normaliseSavedState(views[sourceName])')
        ->toContain("copyStatus.textContent='Duplicated workspace view as '")
        ->toContain("copyStatus.textContent='Saved view limit reached'");
});










