<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('repairs stale default and pin metadata then rewrites canonical storage', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-repair-views"')
        ->toContain('if(defaultName&&!views[defaultName])localStorage.removeItem(defaultSavedViewKey)')
        ->toContain('JSON.stringify([...new Set(pinned)])')
        ->toContain('if(!writeSavedViews(views))return')
        ->toContain("copyStatus.textContent='Repaired saved workspace views'");
});










