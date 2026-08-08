<?php

declare(strict_types=1);

it('uses print-only values instead of editable controls', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('settings-edit-control')
        ->toContain('settings-print-value')
        ->toContain('.settings-edit-control{display:none!important}')
        ->toContain('.settings-print-value{display:inline!important')
        ->toContain("'Enabled'")
        ->toContain("'Disabled'");
});

it('keeps secret masking authoritative in print and screen views', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    expect($view)->toContain('$effective->secret?\'••••••••\'')
        ->not->toContain('type="password"')
        ->not->toContain('data-copy-setting-value');
});
