<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;

    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'app')) {
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

it('documents the role admin latte migration target', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-latte-migration.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-latte-migration.md'));

    assertStringContainsString('RoleAdminController', $contents);
    assertStringContainsString('Latte', $contents);
    assertStringContainsString('thin-controller', $contents);
    assertStringContainsString('CSRF', $contents);
});

it('provides a read only role admin latte readiness audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-role-admin-latte-readiness.php'));

    $source = (string) file_get_contents($rootPath('tools/audit-role-admin-latte-readiness.php'));

    assertStringContainsString('Read-only audit', $source);
    assertStringContainsString('RoleAdminController.php', $source);
    assertStringContainsString('role-admin-latte-readiness.txt', $source);
});

it('generates role admin latte readiness reports in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-admin-latte-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-role-admin-latte-readiness.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-readiness.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-readiness.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-readiness.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-readiness.log');

    assertStringContainsString('# RoleAdminController Latte Migration Readiness', $report);
    assertStringContainsString('Controller path:', $report);
    assertStringContainsString('Errors: 0', $report);
    assertStringContainsString('CONTROLLER_FOUND yes', $log);
});
