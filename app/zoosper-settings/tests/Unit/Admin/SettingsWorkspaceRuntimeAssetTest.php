<?php

declare(strict_types=1);

it('registers the Settings workspace stylesheet and script through the flat manifest contract', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-settings/config/admin_assets.php';

    expect($manifest['zoosper-settings-workspace-style']['type'])->toBe('style')
        ->and($manifest['zoosper-settings-workspace-style']['path'])->toContain('/asset/zoosper-settings/css/settings-workspace.css?v=')
        ->and($manifest['zoosper-settings-workspace-style']['screens'])->toBe(['settings'])
        ->and($manifest['zoosper-settings-workspace-script']['type'])->toBe('script')
        ->and($manifest['zoosper-settings-workspace-script']['path'])->toContain('/asset/zoosper-settings/js/settings-workspace.js?v=')
        ->and($manifest['zoosper-settings-workspace-script']['screens'])->toBe(['settings'])
        ->and($manifest['zoosper-settings-workspace-script']['attributes']['defer'])->toBeTrue();
});

it('exits safely before reading Settings-only DOM on unrelated Admin screens', function (): void {
    $root = dirname(__DIR__, 5);
    $runtime = (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($runtime)->toContain("scopeOptions=document.getElementById('settings-scope-options');if(!type||!key||!label||!scopeOptions)return;const options=JSON.parse(scopeOptions.textContent)")
        ->toContain("workspaceRoot=document.getElementById('settings-workspace');if(!workspaceRoot)return;")
        ->not->toContain("JSON.parse(document.getElementById('settings-scope-options').textContent)");
});










