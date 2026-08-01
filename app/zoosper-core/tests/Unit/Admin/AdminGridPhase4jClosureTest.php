<?php

declare(strict_types=1);

it('keeps the admin grid closure audit and documentation available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-admin-grid-closure.php')->toBeFile()
        ->and($root . '/docs/development/admin-grid-phase-4j-closure.md')->toBeFile();
});

it('passes the source-based admin grid closure audit', function (): void {
    $root = dirname(__DIR__, 5);
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($root . '/tools/audit-admin-grid-closure.php');

    exec($command . ' 2>&1', $output, $exitCode);

    expect($exitCode)->toBe(0)
        ->and(implode("\n", $output))->toContain('ADMIN_GRID_CLOSURE_ERRORS 0')
        ->toContain('Result: OK');
});
