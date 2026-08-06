<?php

declare(strict_types=1);

it('guards navigation-changing saved-view operations when settings forms are dirty', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain("const hasDirtySettingsForms=()=>[...document.querySelectorAll('[data-settings-form]')]")
        ->toContain('const guardWorkspaceOperation=message=>')
        ->toContain("window.confirm(message+' Continue?')")
        ->toContain("guardWorkspaceOperation('Unsaved configuration changes may remain hidden.')")
        ->toContain("guardWorkspaceOperation('Importing views changes workspace navigation.')");
});
