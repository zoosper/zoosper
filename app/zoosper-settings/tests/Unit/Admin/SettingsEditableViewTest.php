<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides an editable-only view from server-authoritative metadata', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('<option value="editable">Editable</option>')
        ->toContain('data-setting-editable="<?= in_array($setting,$editable,true) ? \'true\' : \'false\' ?>"')
        ->toContain("source==='editable'?field.dataset.settingEditable==='true'")
        ->not->toContain('data-setting-value');
});
