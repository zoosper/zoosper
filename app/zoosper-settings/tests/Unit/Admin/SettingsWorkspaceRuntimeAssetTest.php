<?php

declare(strict_types=1);

it('registers the Settings workspace stylesheet and script through the flat manifest contract', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-settings/config/admin_assets.php';

    expect($manifest['zoosper-settings-workspace-style']['type'])->toBe('style')
        ->and($manifest['zoosper-settings-workspace-style']['path'])->toContain('/asset/zoosper-settings/css/settings-workspace.css?v=')
        ->and($manifest['zoosper-settings-workspace-script']['type'])->toBe('script')
        ->and($manifest['zoosper-settings-workspace-script']['path'])->toContain('/asset/zoosper-settings/js/settings-workspace.js?v=')
        ->and($manifest['zoosper-settings-workspace-script']['attributes']['defer'])->toBeTrue();
});
