<?php

declare(strict_types=1);

it('marks and clears the browser-local default view', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-clear-default-view" disabled')
        ->toContain("option.textContent+=' (default)'")
        ->toContain("localStorage.removeItem(defaultSavedViewKey)")
        ->toContain("copyStatus.textContent='Default workspace view cleared'");
});
