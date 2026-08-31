<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('copies value-free saved-view diagnostics', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-view-diagnostics"')
        ->toContain('savedViewCount:Object.keys(views).length')
        ->toContain('pinnedViewCount:pinned.length')
        ->toContain('hasDefault:Boolean(localStorage.getItem(defaultSavedViewKey))')
        ->toContain("copyStatus.textContent='Copied saved-view diagnostics'")
        ->not->toContain('configurationValues:');
});










