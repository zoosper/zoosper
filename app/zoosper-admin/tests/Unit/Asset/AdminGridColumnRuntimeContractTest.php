<?php

declare(strict_types=1);

it('ships one consolidated Grid column runtime with valid JavaScript', function (): void {
    $root = dirname(__DIR__, 5);
    $application = $root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-column-drag.js';
    $package = $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-column-order.js';
    $source = file_get_contents($application);

    expect($source)->not->toBeFalse()
        ->and($application)->toBeFile()
        ->and($package)->toBeFile()
        ->and(hash_file('sha256', $application))->toBe(hash_file('sha256', $package));

    expect($source)
        ->toContain("const LOCKED_KEYS = new Set(['id', 'actions'])")
        ->toContain('syncOrderInputs')
        ->toContain('reflectTableOrder')
        ->toContain("new CustomEvent('zoosper:grid:columns-reordered'")
        ->toContain('Unsaved changes')
        ->toContain('[data-grid-column-move]')
        ->not->toContain('MutationObserver')
        ->not->toContain('cellIndex')
        ->not->toContain('Zoosper Phase 4ZE');

    $node = trim((string) shell_exec('command -v node 2>/dev/null'));
    if ($node === '') {
        test()->markTestSkipped('Node.js is unavailable; source and ownership checks still ran.');
    }

    exec(escapeshellarg($node) . ' --check ' . escapeshellarg($application) . ' 2>&1', $output, $status);
    expect($status)->toBe(0, implode(PHP_EOL, $output));
});

it('uses content-derived versions for the rendered compatibility bridge', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $assets = $manifest['assets'] ?? [];
    $scriptPath = $assets['zoosper-grid-column-drag-script']['path'] ?? '';
    $stylePath = $assets['zoosper-grid-column-drag-style']['path'] ?? '';

    expect($scriptPath)->toMatch('#^/asset/zoosper-admin/js/zoosper-grid-column-drag\.js\?v=[a-f0-9]{12}$#')
        ->and($stylePath)->toMatch('#^/asset/zoosper-admin/css/zoosper-grid-column-drag\.css\?v=[a-f0-9]{12}$#');

    parse_str((string) parse_url($scriptPath, PHP_URL_QUERY), $scriptQuery);
    parse_str((string) parse_url($stylePath, PHP_URL_QUERY), $styleQuery);

    expect($scriptQuery['v'] ?? null)->toBe(substr(hash_file('sha256', $root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-column-drag.js'), 0, 12))
        ->and($styleQuery['v'] ?? null)->toBe(substr(hash_file('sha256', $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-column-drag.css'), 0, 12));
});
