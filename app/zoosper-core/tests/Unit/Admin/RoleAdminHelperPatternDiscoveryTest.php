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

it('documents role admin helper pattern discovery', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-helper-pattern-discovery.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-helper-pattern-discovery.md'));

    assertStringContainsString('form()', $contents);
    assertStringContainsString('html()', $contents);
    assertStringContainsString('RoleAdminController', $contents);
});

it('provides the role admin helper pattern discovery tool', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/discover-role-admin-helper-pattern.php'));

    $source = (string) file_get_contents($rootPath('tools/discover-role-admin-helper-pattern.php'));

    assertStringContainsString('Discover RoleAdminController helper method source', $source);
    assertStringContainsString('method-form.txt', $source);
    assertStringContainsString('method-html.txt', $source);
    assertStringContainsString('extractMethodSource', $source);
});

it('exports role admin helper method context in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-helper-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/discover-role-admin-helper-pattern.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern.log');
    assertDirectoryExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern-source');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern-source' . DIRECTORY_SEPARATOR . 'method-form.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern-source' . DIRECTORY_SEPARATOR . 'method-html.txt');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern.log');

    assertStringContainsString('# RoleAdminController Helper Pattern Discovery', $report);
    assertStringContainsString('HELPER_PATTERN_ERRORS 0', $log);
});
