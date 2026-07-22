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

it('keeps inventory and inspection tools aligned on remaining legacy verify candidates', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/generate-tools-inventory-report.php'));
    assertFileExists($rootPath('tools/inspect-legacy-verify-migration.php'));
    $inventoryDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-inventory-' . bin2hex(random_bytes(6));
    $inspectionDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-inspection-' . bin2hex(random_bytes(6));
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/generate-tools-inventory-report.php')) . ' --output-dir=' . escapeshellarg($inventoryDir), $inventoryOutput, $inventoryExitCode);
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/inspect-legacy-verify-migration.php')) . ' --output-dir=' . escapeshellarg($inspectionDir), $inspectionOutput, $inspectionExitCode);
    assertSame(0, $inventoryExitCode);
    assertSame(0, $inspectionExitCode);
    $inventory = (string) file_get_contents($inventoryDir . DIRECTORY_SEPARATOR . 'tools-inventory.txt');
    $inspection = (string) file_get_contents($inspectionDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-inspection.txt');
    foreach (['tools/verify-module-composer-manifests.php', 'tools/verify-roadmap-planning-docs.php'] as $script) {
        assertStringContainsString($script, $inventory);
        assertStringContainsString($script, $inspection);
    }
    foreach (['tools/verify-project-structure.php', 'tools/verify-runtime-path-safety.php', 'tools/verify-service-provider-manifest-file.php'] as $script) {
        assertStringNotContainsString($script, $inventory);
        assertStringNotContainsString($script, $inspection);
    }
});
