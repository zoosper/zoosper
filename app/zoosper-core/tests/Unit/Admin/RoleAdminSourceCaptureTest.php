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

it('documents role admin source capture before cutover', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-source-capture.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-source-capture.md'));

    assertStringContainsString('RoleAdminController', $contents);
    assertStringContainsString('constructor parameters', $contents);
    assertStringContainsString('render/view', $contents);
    assertStringContainsString('signals', $contents);
});

it('provides a read only role admin source export tool', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/export-role-admin-cutover-source.php'));

    $source = (string) file_get_contents($rootPath('tools/export-role-admin-cutover-source.php'));

    assertStringContainsString('Export exact RoleAdminController source context', $source);
    assertStringContainsString('role-admin-cutover-source.txt', $source);
    assertStringContainsString('Full RoleAdminController source with line numbers', $source);
});

it('exports role admin cutover source context in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-source-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/export-role-admin-cutover-source.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-cutover-source.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-cutover-source.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-cutover-source.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-cutover-source.log');

    assertStringContainsString('# RoleAdminController Cutover Source Capture', $report);
    assertStringContainsString('Full RoleAdminController source with line numbers', $report);
    assertStringContainsString('CONTROLLER_FOUND yes', $log);
    assertStringContainsString('SOURCE_CAPTURE_ERRORS 0', $log);
});
