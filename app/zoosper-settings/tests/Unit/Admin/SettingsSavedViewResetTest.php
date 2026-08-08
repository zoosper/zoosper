<?php

declare(strict_types=1);

it('restores the selected view and clears a stale field fragment', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-restore-view" disabled')
        ->toContain("history.replaceState(null,'',location.pathname+location.search)")
        ->toContain("copyStatus.textContent='Restored saved workspace view '")
        ->toContain('restoreView.addEventListener');
});
