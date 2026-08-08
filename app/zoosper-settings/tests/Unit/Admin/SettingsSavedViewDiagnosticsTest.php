<?php

declare(strict_types=1);

it('copies value-free saved-view diagnostics', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-view-diagnostics"')
        ->toContain('savedViewCount:Object.keys(views).length')
        ->toContain('pinnedViewCount:pinned.length')
        ->toContain('hasDefault:Boolean(localStorage.getItem(defaultSavedViewKey))')
        ->toContain("copyStatus.textContent='Copied saved-view diagnostics'")
        ->not->toContain('configurationValues:');
});
