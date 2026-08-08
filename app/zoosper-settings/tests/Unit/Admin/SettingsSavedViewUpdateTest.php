<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('updates a selected saved view from normalised current workspace state after confirmation', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-update-view" disabled')
        ->toContain("window.confirm('Update saved view '+name+' from the current workspace?')")
        ->toContain('views[name]=normaliseSavedState(workspaceState())')
        ->toContain("copyStatus.textContent='Updated saved workspace view '");
});
