<?php

declare(strict_types=1);

it('reports whether the selected saved view matches the current workspace', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('id="settings-saved-view-state"')
        ->toContain('const savedStateEquals=(left,right)=>')
        ->toContain("'Saved view active':'Modified from saved view'")
        ->toContain('refreshSavedViewState()');
});
