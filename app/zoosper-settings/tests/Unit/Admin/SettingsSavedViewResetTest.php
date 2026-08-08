<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('restores the selected view and clears a stale field fragment', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-restore-view" disabled')
        ->toContain("history.replaceState(null,'',location.pathname+location.search)")
        ->toContain("copyStatus.textContent='Restored saved workspace view '")
        ->toContain('restoreView.addEventListener');
});
