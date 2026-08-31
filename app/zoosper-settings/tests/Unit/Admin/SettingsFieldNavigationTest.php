<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides stable field links and value-free copy-path controls', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('\'id\' => \'setting-\' . str_replace')
        ->toContain('href="#<?= $e($fieldView[\'id\']) ?>"')
        ->toContain('data-copy-setting-path="<?= $e($setting->path) ?>"')
        ->toContain('navigator.clipboard.writeText(path)')
        ->not->toContain('data-copy-setting-value');
});

it('publishes accessible copy feedback and field target focus', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('id="settings-copy-status"')
        ->toContain('role="status"')
        ->toContain("copyStatus.textContent='Copied '+path")
        ->toContain('.settings-field:target');
});










