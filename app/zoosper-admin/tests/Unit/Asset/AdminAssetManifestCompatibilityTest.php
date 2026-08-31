<?php

declare(strict_types=1);

it('supports wrapped and established flat module admin asset manifests', function (): void {
    $root = dirname(__DIR__, 5);
    $registry = (string) file_get_contents($root . '/app/zoosper-admin/src/Asset/AdminAssetRegistry.php');
    $settings = require $root . '/app/zoosper-settings/config/admin_assets.php';
    $auth = require $root . '/app/zoosper-auth/config/admin_assets.php';

    expect($registry)->toContain('$declarations = $this->assetDeclarations($config, $file)')
        ->toContain("array_key_exists('assets', \$config)")
        ->toContain('return $config;')
        ->and($settings)->toHaveKeys([
            'zoosper-settings-workspace-style',
            'zoosper-settings-workspace-script',
        ])
        ->and($auth)->toHaveKey('zoosper-admin-user-two-factor-reset-runtime');
});










