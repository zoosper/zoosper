<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

test('workspace assets are module-owned and avoid inline event handlers', function (): void {
    $base = dirname(__DIR__, 2);
    $config = require $base . '/config/admin_assets.php';
    $script = (string) file_get_contents($base . '/resources/admin/js/grid-workspace.js');

    expect($config['stylesheets'][0]['path'])->toBe('resources/admin/css/grid-workspace.css');
    expect($config['scripts'][0]['path'])->toBe('resources/admin/js/grid-workspace.js');
    expect($config['scripts'][0]['defer'])->toBeTrue();
    expect($script)->not->toContain('onclick=');
    expect($script)->not->toContain('innerHTML');
    expect($script)->toContain("document.querySelectorAll('[data-grid-workspace]')");
});

test('workspace enhancement supports drag and explicit keyboard-operable movement', function (): void {
    $base = dirname(__DIR__, 2);
    $script = (string) file_get_contents($base . '/resources/admin/js/grid-workspace.js');

    expect($script)->toContain("addEventListener('dragstart'")
        ->toContain("addEventListener('dragover'")
        ->toContain("addEventListener('dragend'")
        ->toContain("[data-column-move]")
        ->toContain('syncOrderInputs');
});











