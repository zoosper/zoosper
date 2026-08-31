<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides source filtering with live result counts', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('id="settings-source-filter"')
        ->toContain('<option value="database">Overrides</option>')
        ->toContain('<option value="inherited">Inherited</option>')
        ->toContain('<option value="project">Project controlled</option>')
        ->toContain('<option value="readonly">Read-only</option>')
        ->toContain('id="settings-result-summary"')
        ->toContain("localStorage.setItem('zoosper.settings.sourceFilter'");
});

it('marks every rendered field with source and read-only metadata', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('data-setting-source="<?= $e($effective->source) ?>"')
        ->toContain('data-setting-readonly="<?= $effective->readOnly ? \'true\' : \'false\' ?>"');
});

it('provides expand-all and collapse-all operations without changing persistence', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('id="settings-expand-all"')
        ->toContain('id="settings-collapse-all"')
        ->toContain("expandAll.addEventListener('click'")
        ->toContain("collapseAll.addEventListener('click'")
        ->toContain('action="<?= $e($saveUrl) ?>"')
        ->toContain('formaction="<?= $e($clearUrl) ?>"');
});










