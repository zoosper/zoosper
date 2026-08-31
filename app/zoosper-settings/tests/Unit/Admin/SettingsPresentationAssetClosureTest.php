<?php

declare(strict_types=1);

it('serves Settings presentation through module-owned assets', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-settings/config/assets.php';
    $adminAssets = require $root . '/app/zoosper-settings/config/admin_assets.php';

    expect($manifest)->toHaveKey('zoosper-settings')
        ->and(is_file($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css'))->toBeTrue()
        ->and(is_file($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js'))->toBeTrue()
        ->and($adminAssets)->toHaveKeys([
            'zoosper-settings-workspace-style',
            'zoosper-settings-workspace-script',
        ]);
});

it('keeps executable CSS and JavaScript out of the Settings template', function (): void {
    $root = dirname(__DIR__, 5);
    $view = (string) file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $css = (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $js = (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->not->toContain('<style>')
        ->not->toMatch('/<script(?![^>]*type="application\/json")/')
        ->and(strlen($css))->toBeGreaterThan(10000)
        ->and(strlen($js))->toBeGreaterThan(30000)
        ->and($js)->toContain('localStorage')
        ->toContain("window.addEventListener('beforeprint'");
});










