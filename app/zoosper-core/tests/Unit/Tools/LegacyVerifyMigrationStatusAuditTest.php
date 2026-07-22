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
    $contents = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));

    assertStringContainsString('| `tools/verify-project-structure.php` | migrated |', $contents);
    assertStringContainsString('| `tools/verify-runtime-path-safety.php` | migrated |', $contents);
    assertStringContainsString('| `tools/verify-service-provider-manifest-file.php` | source-owned |', $contents);
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

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-status.txt');

    assertStringContainsString('Entries: 5', $report);
    assertStringContainsString('Errors: 0', $report);
});

it('allows migrated scripts to be absent while source-owned scripts remain present', function () use ($rootPath): void {
    assertFileDoesNotExist($rootPath('tools/verify-project-structure.php'));
    assertFileDoesNotExist($rootPath('tools/verify-runtime-path-safety.php'));

    foreach ([
        'tools/verify-service-provider-manifest-file.php',
        'tools/verify-module-composer-manifests.php',
        'tools/verify-roadmap-planning-docs.php',
    ] as $script) {
        assertFileExists($rootPath($script));
    }
});
