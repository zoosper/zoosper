<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;

    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'tools')) {
            return $current;
        }

        $current = dirname($current);
    }

    fail('Unable to locate Zoosper repository root from ' . __DIR__);
};

$rootPath = static function (string $path = '') use ($repoRootPath): string {
    $root = $repoRootPath();

    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
};

it('documents the legacy verify migration status model', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/legacy-verify-migration-status.md'));
    assertFileExists($rootPath('tools/audit-legacy-verify-migration-status.php'));

    $contents = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));

    assertStringContainsString('source-owned', $contents);
    assertStringContainsString('migrated', $contents);
    assertStringContainsString('| `tools/verify-project-structure.php` | migrated |', $contents);
    assertStringContainsString('| `tools/verify-roadmap-planning-docs.php` | source-owned |', $contents);
});

it('audits the migration status ledger in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-migration-status-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-legacy-verify-migration-status.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-status.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-status.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-status.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-status.log');

    assertStringContainsString('# Legacy Verify Migration Status Audit', $report);
    assertStringContainsString('Entries: 5', $report);
    assertStringContainsString('Errors: 0', $report);
    assertStringContainsString('STATUS_ENTRIES 5', $log);
    assertStringContainsString('STATUS_ERRORS 0', $log);
});

it('allows migrated project structure script to be absent while source-owned scripts remain present', function () use ($rootPath): void {
    assertFileDoesNotExist($rootPath('tools/verify-project-structure.php'));

    foreach ([
        'tools/verify-runtime-path-safety.php',
        'tools/verify-service-provider-manifest-file.php',
        'tools/verify-module-composer-manifests.php',
        'tools/verify-roadmap-planning-docs.php',
    ] as $script) {
        assertFileExists($rootPath($script));
    }
});
