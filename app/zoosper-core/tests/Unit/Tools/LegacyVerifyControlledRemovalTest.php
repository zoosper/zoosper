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

it('documents the controlled legacy verify removal process', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/legacy-verify-controlled-removal.md'));
    assertFileExists($rootPath('tools/remove-migrated-legacy-verify.php'));

    $docs = (string) file_get_contents($rootPath('docs/development/legacy-verify-controlled-removal.md'));

    assertStringContainsString('Dry-run first', $docs);
    assertStringContainsString('--apply', $docs);
    assertStringContainsString('--confirm-pest-coverage', $docs);
    assertStringContainsString('--confirm-remove', $docs);
    assertStringContainsString('equivalent Pest coverage', $docs);
    assertStringContainsString('tools/verify-project-structure.php', $docs);
});

it('keeps legacy verify removal dry-run by default', function () use ($rootPath): void {
    $script = $rootPath('tools/verify-project-structure.php');
    assertFileExists($script);

    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-controlled-removal-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php'))
        . ' --script=tools/verify-project-structure.php'
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($script);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-controlled-removal-verify-project-structure.txt');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-controlled-removal-verify-project-structure.txt');

    assertStringContainsString('Mode: dry-run', $report);
    assertStringContainsString('Result: dry-run only; no files changed', $report);
});

it('refuses apply without explicit confirmations', function () use ($rootPath): void {
    $script = $rootPath('tools/verify-project-structure.php');
    assertFileExists($script);

    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php'))
        . ' --script=tools/verify-project-structure.php --apply';

    exec($command, $output, $exitCode);

    assertTrue($exitCode !== 0);
    assertFileExists($script);
});

it('refuses non allowlisted legacy verify scripts', function () use ($rootPath): void {
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php'))
        . ' --script=tools/verify-static-assets.php';

    exec($command, $output, $exitCode);

    assertTrue($exitCode !== 0);
});

it('refuses path traversal and operational tool removal', function () use ($rootPath): void {
    foreach (['../tools/verify-project-structure.php', 'tools/audit-public-webroot.php'] as $candidate) {
        $command = escapeshellarg(PHP_BINARY)
            . ' '
            . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php'))
            . ' --script='
            . escapeshellarg($candidate);

        exec($command, $output, $exitCode);

        assertTrue($exitCode !== 0);
    }
});

it('keeps the apply gate explicit in source', function () use ($rootPath): void {
    $source = (string) file_get_contents($rootPath('tools/remove-migrated-legacy-verify.php'));

    assertStringContainsString('--apply', $source);
    assertStringContainsString('--confirm-pest-coverage', $source);
    assertStringContainsString('--confirm-remove', $source);
    assertStringContainsString('Dry-run is the default', $source);
    assertStringContainsString('allowedPilotScripts', $source);
    assertStringContainsString('unlink', $source);
});
