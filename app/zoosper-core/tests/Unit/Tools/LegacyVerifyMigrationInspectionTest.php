<?php

declare(strict_types=1);

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

it('provides a read only legacy verify migration inspection command', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/inspect-legacy-verify-migration.php'));
    assertFileExists($rootPath('docs/development/legacy-verify-migration-tooling.md'));

    $source = (string) file_get_contents($rootPath('tools/inspect-legacy-verify-migration.php'));

    assertStringContainsString('legacy-verify-migration-inspection.txt', $source);
    assertStringContainsString('verify-*.php', $source);
    assertStringContainsString('This command is intentionally read-only', $source);
});

it('generates legacy verify migration inspection reports in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-legacy-verify-inspection-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/inspect-legacy-verify-migration.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-inspection.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-inspection.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'legacy-verify-migration-inspection.txt');

    assertStringContainsString('# Legacy Verify Migration Inspection', $report);
    assertStringContainsString('Legacy verify scripts:', $report);
    assertStringContainsString('tools/verify-', $report);
});
