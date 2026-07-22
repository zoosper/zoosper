<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;
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

it('documents the ledger aware removal gate', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/legacy-verify-ledger-aware-removal.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/legacy-verify-ledger-aware-removal.md'));

    assertStringContainsString('source-owned', $contents);
    assertStringContainsString('migrated', $contents);
    assertStringContainsString('docs/development/legacy-verify-migration-status.md', $contents);
});

it('refuses apply for source owned scripts even with confirmation flags', function () use ($rootPath): void {
    $script = $rootPath('tools/verify-project-structure.php');
    assertFileExists($script);

    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php'))
        . ' --script=tools/verify-project-structure.php --apply --confirm-pest-coverage --confirm-remove';

    exec($command, $output, $exitCode);

    assertTrue($exitCode !== 0);
    assertFileExists($script);
});

it('keeps dry run available for source owned scripts', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-ledger-aware-removal-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php'))
        . ' --script=tools/verify-project-structure.php'
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);

    $reportPath = $outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-controlled-removal-verify-project-structure.txt';
    assertFileExists($reportPath);

    $report = (string) file_get_contents($reportPath);

    assertStringContainsString('Mode: dry-run', $report);
    assertStringContainsString('Migration status: source-owned', $report);
    assertStringContainsString('Result: dry-run only; no files changed', $report);
});

it('keeps removal helper source tied to the migration status ledger', function () use ($rootPath): void {
    $source = (string) file_get_contents($rootPath('tools/remove-migrated-legacy-verify.php'));

    assertStringContainsString('legacy-verify-migration-status.md', $source);
    assertStringContainsString('migrationStatusFor', $source);
    assertStringContainsString('$status !== \'migrated\'', $source);
});
