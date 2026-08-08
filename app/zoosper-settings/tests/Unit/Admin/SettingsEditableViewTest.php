<?php

declare(strict_types=1);

it('provides an editable-only view from server-authoritative metadata', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('<option value="editable">Editable</option>')
        ->toContain('data-setting-editable="<?= in_array($setting,$editable,true) ? \'true\' : \'false\' ?>"')
        ->toContain("source==='editable'?field.dataset.settingEditable==='true'")
        ->not->toContain('data-setting-value');
});
