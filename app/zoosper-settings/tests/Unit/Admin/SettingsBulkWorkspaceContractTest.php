<?php

declare(strict_types=1);

it('supports multiple module-owned categories, stable sections and group anchors', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain('settings-section-nav')
        ->toContain('id="section-<?= $e($section->id) ?>"')
        ->toContain('id="group-<?= $e($section->id.\'-\'.$group->id) ?>"')
        ->toContain('data-setting-field');
});

it('keeps Mail secrets redacted and excludes password controls', function (): void {
    $root = dirname(__DIR__, 5);
    $config = require $root . '/app/zoosper-mail/config/admin_settings.php';
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($config[0]['category'])->toBe('communication')
        ->and($view)->toContain('••••••••')
        ->toContain('$effective->secret')
        ->not->toContain('type="password"');
});
