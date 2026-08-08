<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('guards navigation-changing saved-view operations when settings forms are dirty', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("const hasDirtySettingsForms=()=>[...document.querySelectorAll('[data-settings-form]')]")
        ->toContain('const guardWorkspaceOperation=message=>')
        ->toContain("window.confirm(message+' Continue?')")
        ->toContain("guardWorkspaceOperation('Unsaved configuration changes may remain hidden.')")
        ->toContain("guardWorkspaceOperation('Importing views changes workspace navigation.')");
});
