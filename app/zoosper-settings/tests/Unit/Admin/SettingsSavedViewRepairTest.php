<?php

declare(strict_types=1);

it('repairs stale default and pin metadata then rewrites canonical storage', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('id="settings-repair-views"')
        ->toContain('if(defaultName&&!views[defaultName])localStorage.removeItem(defaultSavedViewKey)')
        ->toContain('JSON.stringify([...new Set(pinned)])')
        ->toContain('if(!writeSavedViews(views))return')
        ->toContain("copyStatus.textContent='Repaired saved workspace views'");
});
