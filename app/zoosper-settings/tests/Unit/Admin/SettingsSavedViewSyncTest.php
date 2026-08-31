<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('refreshes saved-view controls when another tab changes local storage', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("window.addEventListener('storage'")
        ->toContain('if(event.key===savedViewsKey)')
        ->toContain('renderSavedViews(selected)')
        ->toContain("copyStatus.textContent='Saved views updated in another tab'")
        ->toContain('if(event.key===defaultSavedViewKey)');
});










