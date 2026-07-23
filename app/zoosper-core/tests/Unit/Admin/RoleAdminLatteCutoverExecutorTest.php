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

it('documents the role admin latte cutover executor', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-latte-cutover-executor.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-latte-cutover-executor.md'));

    assertStringContainsString('RoleAdminController', $contents);
    assertStringContainsString('--apply', $contents);
    assertStringContainsString('strict closeout', $contents);
});

it('provides the guarded role admin latte cutover executor', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/apply-role-admin-latte-cutover.php'));

    $source = (string) file_get_contents($rootPath('tools/apply-role-admin-latte-cutover.php'));

    assertStringContainsString('Guarded RoleAdminController Latte cutover executor', $source);
    assertStringContainsString('role-admin-latte-cutover-executor.txt', $source);
    assertStringContainsString('detectSafePattern', $source);
});

it('runs the role admin latte cutover executor in read only mode', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-executor-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/apply-role-admin-latte-cutover.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-cutover-executor.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-cutover-executor.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-cutover-executor.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-cutover-executor.log');

    assertStringContainsString('# RoleAdminController Latte Cutover Executor', $report);
    assertStringContainsString('MODE read-only', $log);
});
