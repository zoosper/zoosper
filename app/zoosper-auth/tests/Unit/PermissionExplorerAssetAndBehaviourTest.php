<?php

declare(strict_types=1);

it('declares and ships contract-safe Permission Explorer assets', function (): void {
    $module = dirname(__DIR__, 2);
    $assets = require $module . '/config/admin_assets.php';
    $runtime = file_get_contents($module . '/resources/assets/admin/js/permission-explorer.js');
    expect($assets)->toHaveKeys(['zoosper-permission-explorer-style', 'zoosper-permission-explorer-runtime'])
        ->and($runtime)->toContain('permission_ids[]')->toContain('Expand all')->toContain('Collapse all')->toContain("event.key === 'Escape'")->toContain('!checkbox.disabled')->not->toContain('fetch(')->not->toContain('.submit(');
});
