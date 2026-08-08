<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('marks and clears the browser-local default view', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-clear-default-view" disabled')
        ->toContain("option.textContent+=' (default)'")
        ->toContain("localStorage.removeItem(defaultSavedViewKey)")
        ->toContain("copyStatus.textContent='Default workspace view cleared'");
});
