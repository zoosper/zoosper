<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertDirectoryExists;
use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertStringStartsWith;
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

it('keeps runtime report directory policy anchored under var reports', function () use ($rootPath): void {
    assertDirectoryExists($rootPath('var'));

    $reports = $rootPath('var/reports');
    if (! is_dir($reports)) {
        mkdir($reports, 0775, true);
    }

    assertDirectoryExists($reports);
    assertStringStartsWith($rootPath('var'), $reports);
});

it('records runtime path safety legacy script as migrated after retirement', function () use ($rootPath): void {
    assertFileDoesNotExist($rootPath('tools/verify-runtime-path-safety.php'));
    assertFileExists($rootPath('docs/development/legacy-verify-migration-status.md'));

    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));

    assertStringContainsString('| `tools/verify-runtime-path-safety.php` | migrated |', $status);
});

it('keeps public webroot and runtime policy tooling discoverable', function () use ($rootPath): void {
    assertDirectoryExists($rootPath('public'));
    assertFileExists($rootPath('tools/public-webroot-policy.php'));
    assertFileExists($rootPath('tools/audit-public-webroot.php'));
    assertFileExists($rootPath('tools/clean-public-runtime-directories.php'));
});

it('provides read only evidence tooling for the runtime path safety migration candidate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-verify-runtime-path-safety-migration.php'));
    assertFileExists($rootPath('docs/development/verify-runtime-path-safety-migration.md'));

    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-runtime-path-migration-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-verify-runtime-path-safety-migration.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'verify-runtime-path-safety-migration.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'verify-runtime-path-safety-migration.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'verify-runtime-path-safety-migration.txt');

    assertStringContainsString('Legacy script: tools/verify-runtime-path-safety.php', $report);
    assertStringContainsString('Migration status: migrated', $report);
    assertStringContainsString('Errors: 0', $report);
});
