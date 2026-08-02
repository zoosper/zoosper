<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

it('owns the canonical key-driven column order runtime', function (): void {
    $root = dirname(__DIR__, 4);
    $path = $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-column-order.js';
    $source = file_get_contents($path);

    expect($source)->not->toBeFalse()
        ->and($source)
        ->toContain('[data-grid-column-list]')
        ->toContain('.grid-compact-column[data-column-key]')
        ->toContain("new Set(['id', 'actions'])")
        ->toContain('syncOrderInputs')
        ->toContain('reflectTableOrder')
        ->toContain('data-grid-column')
        ->toContain('zoosper:grid:columns-reordered')
        ->toContain('[data-grid-column-move]')
        ->not->toContain('cellIndex');

    $node = trim((string) shell_exec('command -v node 2>/dev/null'));
    if ($node === '') {
        test()->markTestSkipped('Node.js is unavailable; source contract checks still ran.');
    }

    exec(escapeshellarg($node) . ' --check ' . escapeshellarg($path) . ' 2>&1', $output, $status);
    expect($status)->toBe(0, implode(PHP_EOL, $output));
});
