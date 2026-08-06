<?php

declare(strict_types=1);

it('copies value-free saved-view diagnostics', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('id="settings-view-diagnostics"')
        ->toContain('savedViewCount:Object.keys(views).length')
        ->toContain('pinnedViewCount:pinned.length')
        ->toContain('hasDefault:Boolean(localStorage.getItem(defaultSavedViewKey))')
        ->toContain("copyStatus.textContent='Copied saved-view diagnostics'")
        ->not->toContain('configurationValues:');
});
