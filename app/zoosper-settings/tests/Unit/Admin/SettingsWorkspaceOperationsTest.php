<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('keeps source metadata display controls and scoped mutation contracts', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('id="settings-source-filter"')
        ->toContain('<option value="database">Overrides</option>')
        ->toContain('<option value="readonly">Read-only</option>')
        ->toContain('data-setting-source="<?= $e($effective->source) ?>"')
        ->toContain('data-setting-readonly="<?= $effective->readOnly ? \'true\' : \'false\' ?>"')
        ->toContain('id="settings-expand-all"')
        ->toContain('id="settings-collapse-all"')
        ->toContain('action="<?= $e($saveUrl) ?>"')
        ->toContain('formaction="<?= $e($clearUrl) ?>"');
});
