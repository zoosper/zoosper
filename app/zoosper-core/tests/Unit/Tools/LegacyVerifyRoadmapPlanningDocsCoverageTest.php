<?php

declare(strict_types=1);

$repoRootPath = static function (): string {
    $current = __DIR__;
    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'tools')) {
            return $current;
        }
        $current = dirname($current);
    }
    \PHPUnit\Framework\fail('Unable to locate Zoosper repository root from ' . __DIR__);
};

$rootPath = static function (string $path = '') use ($repoRootPath): string {
    $root = $repoRootPath();
    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
};

use function PHPUnit\Framework\assertDirectoryExists;
use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;

it('records roadmap planning docs legacy script as migrated after retirement', function () use ($rootPath): void {
    assertFileDoesNotExist($rootPath('tools/verify-roadmap-planning-docs.php'));
    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));
    assertStringContainsString('| `tools/verify-roadmap-planning-docs.php` | migrated |', $status);
});

it('keeps development documentation and closeout docs discoverable', function () use ($rootPath): void {
    assertDirectoryExists($rootPath('docs/development'));
    assertFileExists($rootPath('docs/development/legacy-verify-pilot-closeout.md'));
    assertFileExists($rootPath('docs/development/verify-roadmap-planning-docs-migration.md'));
});

it('provides read only evidence tooling for the roadmap planning docs migration candidate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-verify-roadmap-planning-docs-migration.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-roadmap-docs-migration-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/audit-verify-roadmap-planning-docs-migration.php')) . ' --output-dir=' . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'verify-roadmap-planning-docs-migration.txt');
    assertStringContainsString('Legacy script: tools/verify-roadmap-planning-docs.php', $report);
    assertStringContainsString('Migration status: migrated', $report);
    assertStringContainsString('Errors: 0', $report);
});
