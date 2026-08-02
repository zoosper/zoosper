<?php

declare(strict_types=1);

it('ships syntactically valid admin grid drag and reflection JavaScript', function (): void {
    $moduleRoot = dirname(__DIR__, 3);
    $path = $moduleRoot . '/resources/assets/js/zoosper-grid-column-drag.js';
    $source = file_get_contents($path);

    expect($source)->not->toBeFalse();
    expect($source)
        ->toContain('Zoosper Phase 4ZE: live table reflection')
        ->toContain('[data-grid-column-list]')
        ->toContain('data-grid-column')
        ->toContain('MutationObserver')
        ->toContain('Unsaved changes')
        ->not->toContain('\\n\\n/* Zoosper Phase 4ZE:')
        ->not->toContain('cellIndex');

    $node = trim((string) shell_exec('command -v node 2>/dev/null'));
    if ($node === '') {
        test()->markTestSkipped('Node.js is unavailable; source contract checks still ran.');
    }

    $command = escapeshellarg($node) . ' --check ' . escapeshellarg($path) . ' 2>&1';
    exec($command, $output, $exitCode);

    expect($exitCode)->toBe(0, implode(PHP_EOL, $output));
});
