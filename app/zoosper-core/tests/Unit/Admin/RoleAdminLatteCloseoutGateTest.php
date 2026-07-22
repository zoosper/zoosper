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

it('documents role admin latte closeout criteria and handoff', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-latte-closeout.md'));
    assertFileExists($rootPath('docs/development/role-admin-latte-closeout-handoff.md'));

    $criteria = (string) file_get_contents($rootPath('docs/development/role-admin-latte-closeout.md'));
    $handoff = (string) file_get_contents($rootPath('docs/development/role-admin-latte-closeout-handoff.md'));

    assertStringContainsString('CLOSEOUT_STATUS closed', $criteria);
    assertStringContainsString('CLOSEOUT_STATUS open', $handoff);
});

it('provides the role admin latte closeout audit gate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-role-admin-latte-closeout.php'));

    $source = (string) file_get_contents($rootPath('tools/audit-role-admin-latte-closeout.php'));

    assertStringContainsString('RoleAdminController Latte Closeout Gate', $source);
    assertStringContainsString('--enforce-closed', $source);
    assertStringContainsString('CLOSEOUT_STATUS', $source);
});

it('generates a role admin latte closeout report in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-closeout-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-role-admin-latte-closeout.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-closeout.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-closeout.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-closeout.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-closeout.log');

    assertStringContainsString('# RoleAdminController Latte Closeout Gate', $report);
    assertStringContainsString('Closeout status:', $report);
    assertStringContainsString('CLOSEOUT_STATUS', $log);
});
