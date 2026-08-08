<?php

declare(strict_types=1);

it('supports copying and clearing the current workspace target', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-copy-view"')
        ->toContain('id="settings-clear-target"')
        ->toContain('const url=buildWorkspaceUrl()')
        ->toContain("history.replaceState(null,'',location.pathname+location.search)")
        ->not->toContain('data-copy-setting-value');
});
