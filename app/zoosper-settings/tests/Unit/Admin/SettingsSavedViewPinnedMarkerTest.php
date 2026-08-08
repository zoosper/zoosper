<?php

declare(strict_types=1);

it('marks pinned views in the selector and refreshes pin changes across tabs', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain("option.textContent='★ '+option.textContent")
        ->toContain('if(event.key===pinnedSavedViewsKey)')
        ->toContain("copyStatus.textContent='Pinned saved views updated in another tab'");
});
