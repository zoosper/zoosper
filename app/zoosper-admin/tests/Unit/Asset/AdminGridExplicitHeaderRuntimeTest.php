<?php

declare(strict_types=1);

it('relies on explicit server-rendered header keys without positional inference', function (): void {
    $root = dirname(__DIR__, 5);
    $application = $root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-column-drag.js';
    $package = $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-column-order.js';
    $source = file_get_contents($application);

    expect($source)->not->toBeFalse()
        ->and(hash_file('sha256', $application))->toBe(hash_file('sha256', $package))
        ->and($source)->toContain('reflectTableOrder')
        ->and($source)->toContain('data-grid-column')
        ->and($source)->not->toContain('ensureCompatibilityHeaderKeys')
        ->and($source)->not->toContain('cells[index]');

    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $path = $manifest['assets']['zoosper-grid-column-drag-script']['path'] ?? '';
    $hash = substr(hash_file('sha256', $application), 0, 12);
    expect($path)->toEndWith('?v=' . $hash);
});
