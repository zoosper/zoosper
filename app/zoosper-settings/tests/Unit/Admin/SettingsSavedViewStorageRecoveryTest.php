<?php

declare(strict_types=1);

it('recovers legacy saved-view storage and reports local-storage write failures', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('const writeSavedViews=views=>{try{')
        ->toContain("copyStatus.textContent='Unable to store saved views in this browser'")
        ->toContain('const recoverSavedViewsStorage=()=>')
        ->toContain('recoverSavedViewsStorage();renderSavedViews');
});

it('does not report save update or import success after a storage failure', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('if(!writeSavedViews(views))return;renderSavedViews(name)')
        ->toContain("if(!writeSavedViews(bounded))return;renderSavedViews('')");
});
