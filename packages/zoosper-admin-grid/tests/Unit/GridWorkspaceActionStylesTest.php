<?php

declare(strict_types=1);

it('publishes the compact Grid workspace action stylesheet', function (): void {
    $root = dirname(__DIR__, 4);
    $assets = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $style = $assets['assets']['zoosper-admin-grid-workspace-actions-style'] ?? null;

    expect($style)->toBeArray()
        ->and($style['type'] ?? null)->toBe('style')
        ->and($style['path'] ?? null)->toBe('/asset/zoosper-admin-grid/css/grid-workspace-actions.css?v=7c-ui1')
        ->and(is_file($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-workspace-actions.css'))->toBeTrue();
});

it('keeps mutation actions compact and responsive', function (): void {
    $root = dirname(__DIR__, 4);
    $css = file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/css/grid-workspace-actions.css');

    expect($css)->not->toBeFalse()
        ->and($css)->toContain('.grid-workspace__mutations')
        ->and($css)->toContain('.grid-workspace__mutation-form')
        ->and($css)->toContain('grid-template-columns')
        ->and($css)->toContain('@media (max-width: 720px)');
});
