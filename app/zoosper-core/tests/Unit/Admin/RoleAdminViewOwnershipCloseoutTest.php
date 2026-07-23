<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertStringNotContainsString;
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

it('documents final role admin latte closeout', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-latte-migration-closeout.md'));
    assertFileExists($rootPath('docs/development/phase-1.38-closeout-handoff.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-latte-migration-closeout.md'));
    assertStringContainsString('renderRoleView', $contents);
    assertStringContainsString('audit-role-admin-view-ownership.php', $contents);
});

it('keeps role admin controller free of large inline markup after view cutover', function () use ($rootPath): void {
    $controller = (string) file_get_contents($rootPath('app/zoosper-admin/src/Controller/RoleAdminController.php'));
    assertStringContainsString('renderRoleView', $controller);
    assertStringNotContainsString('<form', $controller);
    assertStringNotContainsString('<table', $controller);
    assertStringNotContainsString('<input', $controller);
    assertStringNotContainsString('<label', $controller);
    assertStringNotContainsString('<<<', $controller);
});

it('provides role admin view ownership audit tooling', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-role-admin-view-ownership.php'));
    $source = (string) file_get_contents($rootPath('tools/audit-role-admin-view-ownership.php'));
    assertStringContainsString('Strict ownership audit', $source);
    assertStringContainsString('role-admin-view-ownership.txt', $source);
});

it('runs role admin view ownership audit in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-ownership-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-role-admin-view-ownership.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-view-ownership.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-view-ownership.log');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-view-ownership.log');
    assertStringContainsString('VIEW_OWNERSHIP_ERRORS 0', $log);
});
