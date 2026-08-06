<?php

declare(strict_types=1);

it('derives the module filter from discovered module-owned sections', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('$settingsModules[$moduleSection->module] = $moduleSection->module')
        ->toContain('ksort($settingsModules)')
        ->toContain('id="settings-module-filter"')
        ->toContain('<?php foreach($settingsModules as $settingsModule): ?>')
        ->toContain('data-settings-module="<?= $e($section->module) ?>"');
});

it('composes remembered module filtering with search and source views', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain("moduleMatch=module==='all'||section.dataset.settingsModule===module")
        ->toContain("localStorage.setItem('zoosper.settings.moduleFilter',moduleFilter.value)")
        ->toContain("localStorage.getItem('zoosper.settings.moduleFilter')")
        ->toContain("localStorage.removeItem('zoosper.settings.moduleFilter')")
        ->toContain("module!=='all'?'Module: '");
});
