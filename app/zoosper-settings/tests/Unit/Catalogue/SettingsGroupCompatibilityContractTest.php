<?php

declare(strict_types=1);

it('supports explicit groups and normalises legacy settings into General', function (): void {
    $root = dirname(__DIR__, 5);
    $loader = file_get_contents($root . '/app/zoosper-settings/src/Catalogue/ModuleSettingsCatalogueLoader.php');
    $config = require $root . '/app/zoosper-settings/config/admin_settings.php';

    expect($loader)->toContain('isset($raw[\'groups\']) && isset($raw[\'settings\'])')
        ->toContain("id: 'general'")
        ->toContain("label: 'General'")
        ->and($config[0])->toHaveKey('groups')
        ->not->toHaveKey('settings')
        ->and($config[0]['groups'])->toHaveCount(3);
});










