<?php

declare(strict_types=1);

it('updates a selected saved view from normalised current workspace state after confirmation', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-update-view" disabled')
        ->toContain("window.confirm('Update saved view '+name+' from the current workspace?')")
        ->toContain('views[name]=normaliseSavedState(workspaceState())')
        ->toContain("copyStatus.textContent='Updated saved workspace view '");
});
