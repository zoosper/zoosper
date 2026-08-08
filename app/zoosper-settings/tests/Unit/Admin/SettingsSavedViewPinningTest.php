<?php

declare(strict_types=1);

it('pins views locally and sorts pinned names first', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-pin-view" disabled')
        ->toContain("pinnedSavedViewsKey='zoosper.settings.pinnedSavedViews'")
        ->toContain("'Unpin view':'Pin view'")
        ->toContain('Number(pinned.includes(b))-Number(pinned.includes(a))')
        ->toContain("copyStatus.textContent=(next.includes(name)?'Pinned ':'Unpinned ')+name");
});
