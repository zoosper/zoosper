<?php

declare(strict_types=1);

it('duplicates a selected view through bounded normalisation', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-duplicate-view" disabled')
        ->toContain("window.prompt('Duplicate workspace view as',sourceName+' copy')")
        ->toContain('views[name]=normaliseSavedState(views[sourceName])')
        ->toContain("copyStatus.textContent='Duplicated workspace view as '")
        ->toContain("copyStatus.textContent='Saved view limit reached'");
});
