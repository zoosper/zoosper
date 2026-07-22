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
$rootPath = static fn (string $path = ''): string => ($r = $repoRootPath()) && $path === '' ? $r : $r . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);

it('documents the controlled legacy verify removal process', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/legacy-verify-controlled-removal.md'));
    assertFileExists($rootPath('tools/remove-migrated-legacy-verify.php'));
});

it('keeps source-owned legacy verify removal dry-run by default', function () use ($rootPath): void {
    $script = $rootPath('tools/verify-module-composer-manifests.php');
    assertFileExists($script);
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-controlled-removal-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php')) . ' --script=tools/verify-module-composer-manifests.php --output-dir=' . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($script);
    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-controlled-removal-verify-module-composer-manifests.txt');
    assertStringContainsString('Mode: dry-run', $report);
    assertStringContainsString('Result: dry-run only; no files changed', $report);
});

it('refuses apply for source-owned scripts even with explicit confirmations', function () use ($rootPath): void {
    $script = $rootPath('tools/verify-module-composer-manifests.php');
    assertFileExists($script);
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php')) . ' --script=tools/verify-module-composer-manifests.php --apply --confirm-pest-coverage --confirm-remove';
    exec($command, $output, $exitCode);
    assertTrue($exitCode !== 0);
    assertFileExists($script);
});

it('keeps the apply gate explicit in source', function () use ($rootPath): void {
    $source = (string) file_get_contents($rootPath('tools/remove-migrated-legacy-verify.php'));
    assertStringContainsString('migrationStatusFor', $source);
    assertStringContainsString('unlink', $source);
});
