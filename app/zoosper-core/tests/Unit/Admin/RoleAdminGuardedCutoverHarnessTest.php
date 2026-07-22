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

it('documents the guarded role admin cutover contract', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-guarded-cutover.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-guarded-cutover.md'));

    assertStringContainsString('RoleAdminController', $contents);
    assertStringContainsString('guarded cutover', $contents);
    assertStringContainsString('--apply', $contents);
});

it('provides the guarded role admin cutover harness', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/guard-role-admin-controller-cutover.php'));

    $source = (string) file_get_contents($rootPath('tools/guard-role-admin-controller-cutover.php'));

    assertStringContainsString('Guarded RoleAdminController', $source);
    assertStringContainsString('role-admin-guarded-cutover.txt', $source);
    assertStringContainsString('detectSafePattern', $source);
});

it('runs the guarded role admin cutover harness in read only mode', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-guard-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/guard-role-admin-controller-cutover.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-guarded-cutover.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-guarded-cutover.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-guarded-cutover.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-guarded-cutover.log');

    assertStringContainsString('# Guarded RoleAdminController Cutover Report', $report);
    assertStringContainsString('No source changes were made.', $report);
    assertStringContainsString('MODE read-only', $log);
});
