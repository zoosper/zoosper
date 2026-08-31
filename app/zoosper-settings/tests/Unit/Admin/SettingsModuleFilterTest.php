<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('derives the module filter from discovered module-owned sections', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('\'settingsModules\' => $modules')
        ->toContain('ksort($modules)')
        ->toContain('id="settings-module-filter"')
        ->toContain('<?php foreach($settingsModules as $settingsModule): ?>')
        ->toContain('data-settings-module="<?= $e($section->module) ?>"');
});

it('composes remembered module filtering with search and source views', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("moduleMatch=module==='all'||section.dataset.settingsModule===module")
        ->toContain("localStorage.setItem('zoosper.settings.moduleFilter',moduleFilter.value)")
        ->toContain("localStorage.getItem('zoosper.settings.moduleFilter')")
        ->toContain("localStorage.removeItem('zoosper.settings.moduleFilter')")
        ->toContain("module!=='all'?'Module: '");
});










