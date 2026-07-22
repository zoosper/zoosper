<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertStringNotContainsString;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;
    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'tools')) return $current;
        $current = dirname($current);
    }
    fail('Unable to locate Zoosper repository root from ' . __DIR__);
};
$rootPath = static fn (string $path = ''): string => ($r = $repoRootPath()) && $path === '' ? $r : $r . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);

it('provides a deterministic tools inventory report generator', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/generate-tools-inventory-report.php'));
});

it('generates tools inventory reports in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-tools-inventory-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/generate-tools-inventory-report.php')) . ' --output-dir=' . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'tools-inventory.txt');
});

it('classifies remaining legacy verify scripts as Pest migration candidates', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-tools-inventory-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/generate-tools-inventory-report.php')) . ' --output-dir=' . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'tools-inventory.txt');
    assertStringContainsString('tools/verify-module-composer-manifests.php', $report);
    foreach (['tools/verify-project-structure.php', 'tools/verify-runtime-path-safety.php', 'tools/verify-service-provider-manifest-file.php'] as $script) {
        assertStringNotContainsString($script, $report);
    }
});
