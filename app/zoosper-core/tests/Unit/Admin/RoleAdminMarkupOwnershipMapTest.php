<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertDirectoryExists;
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

it('documents role admin markup ownership mapping', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-markup-ownership-map.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-markup-ownership-map.md'));
    assertStringContainsString('RoleAdminController', $contents);
    assertStringContainsString('Markup Ownership Map', $contents);
    assertStringContainsString('form()', $contents);
});

it('provides the role admin markup ownership scanner', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/discover-role-admin-markup-owners.php'));
    $source = (string) file_get_contents($rootPath('tools/discover-role-admin-markup-owners.php'));
    assertStringContainsString('Discover all RoleAdminController methods', $source);
    assertStringContainsString('role-admin-markup-owners.txt', $source);
    assertStringContainsString('markupSignals', $source);
});

it('exports role admin markup ownership in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-markup-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/discover-role-admin-markup-owners.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-markup-owners.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-markup-owners.log');
    assertDirectoryExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-markup-owners-source');
    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-markup-owners.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-markup-owners.log');
    assertStringContainsString('# RoleAdminController Markup Ownership Map', $report);
    assertStringContainsString('MARKUP_OWNER_ERRORS 0', $log);
});
