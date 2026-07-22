<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertStringNotContainsString;
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

it('provides a deterministic tools inventory report generator', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/generate-tools-inventory-report.php'));
    assertFileExists($rootPath('docs/development/tools-inventory-report-generation.md'));

    $tool = (string) file_get_contents($rootPath('tools/generate-tools-inventory-report.php'));

    assertStringContainsString('tools-inventory.txt', $tool);
    assertStringContainsString('tools-inventory.log', $tool);
    assertStringContainsString('MIGRATE_TO_PEST', $tool);
    assertStringContainsString('KEEP_OPS', $tool);
    assertStringContainsString('DELETE_NOW', $tool);
    assertStringContainsString('REVIEW', $tool);
});

it('generates tools inventory reports in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-tools-inventory-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/generate-tools-inventory-report.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'tools-inventory.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'tools-inventory.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'tools-inventory.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'tools-inventory.log');

    assertStringContainsString('## ZOOSPER CMS - TOOLS INVENTORY', $report);
    assertStringContainsString('MIGRATE_TO_PEST', $report);
    assertStringContainsString('KEEP_OPS', $report);
    assertStringContainsString('DELETE_NOW', $report);
    assertStringContainsString('REVIEW', $report);
    assertStringContainsString('PCI note', $report);
    assertStringContainsString('Tools inventory written to:', $log);
});

it('classifies remaining legacy verify scripts as Pest migration candidates', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-tools-inventory-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/generate-tools-inventory-report.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'tools-inventory.txt');

    assertStringContainsString('tools/verify-runtime-path-safety.php', $report);
    assertStringNotContainsString('tools/verify-project-structure.php', $report);
    assertStringContainsString('### [MIGRATE_TO_PEST]', $report);
    assertTrue(! str_contains($report, '### [DELETE_NOW]  (1)'));
});
