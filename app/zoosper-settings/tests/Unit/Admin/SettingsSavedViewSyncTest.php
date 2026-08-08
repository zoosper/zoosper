<?php

declare(strict_types=1);

it('refreshes saved-view controls when another tab changes local storage', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain("window.addEventListener('storage'")
        ->toContain('if(event.key===savedViewsKey)')
        ->toContain('renderSavedViews(selected)')
        ->toContain("copyStatus.textContent='Saved views updated in another tab'")
        ->toContain('if(event.key===defaultSavedViewKey)');
});
