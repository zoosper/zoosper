<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('reports whether the selected saved view matches the current workspace', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-saved-view-state"')
        ->toContain('const savedStateEquals=(left,right)=>')
        ->toContain("'Saved view active':'Modified from saved view'")
        ->toContain('refreshSavedViewState()');
});










