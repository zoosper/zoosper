<?php

declare(strict_types=1);

it('imports saved views from a bounded JSON file', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('id="settings-import-views-file" accept="application/json,.json" hidden')
        ->toContain("importViews.addEventListener('click',()=>importViewsFile.click())")
        ->toContain('if(file.size>262144)')
        ->toContain('applyImportedViews(await file.text())')
        ->toContain("copyStatus.textContent='Saved views JSON is too large'");
});
