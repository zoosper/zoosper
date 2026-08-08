<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('imports saved views from a bounded JSON file', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-import-views-file" accept="application/json,.json" hidden')
        ->toContain("importViews.addEventListener('click',()=>importViewsFile.click())")
        ->toContain('if(file.size>262144)')
        ->toContain('applyImportedViews(await file.text())')
        ->toContain("copyStatus.textContent='Saved views JSON is too large'");
});
