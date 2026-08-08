<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('recovers legacy saved-view storage and reports local-storage write failures', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('const writeSavedViews=views=>{try{')
        ->toContain("copyStatus.textContent='Unable to store saved views in this browser'")
        ->toContain('const recoverSavedViewsStorage=()=>')
        ->toContain('recoverSavedViewsStorage();renderSavedViews');
});

it('does not report save update or import success after a storage failure', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('if(!writeSavedViews(views))return;renderSavedViews(name)')
        ->toContain("if(!writeSavedViews(bounded))return;renderSavedViews('')");
});
