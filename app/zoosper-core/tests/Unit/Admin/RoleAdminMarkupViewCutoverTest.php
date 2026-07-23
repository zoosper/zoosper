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

it('documents role admin markup view cutover', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-markup-view-cutover.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-markup-view-cutover.md'));
    assertStringContainsString('RoleAdminController', $contents);
    assertStringContainsString('index', $contents);
    assertStringContainsString('permissionTree', $contents);
});

it('provides role admin view partials and cutover tool', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/apply-role-admin-markup-view-cutover.php'));
    assertFileExists($rootPath('app/zoosper-admin/resources/views/admin/roles/index.php'));
    assertFileExists($rootPath('app/zoosper-admin/resources/views/admin/roles/form.php'));
    assertFileExists($rootPath('app/zoosper-admin/resources/views/admin/roles/permission-tree.php'));
    assertFileExists($rootPath('app/zoosper-admin/resources/views/admin/roles/user-assignment.php'));

    $tool = (string) file_get_contents($rootPath('tools/apply-role-admin-markup-view-cutover.php'));
    assertStringContainsString('Guarded source-specific RoleAdminController markup view cutover', $tool);
    assertStringContainsString('role-admin-markup-view-cutover.txt', $tool);
});

it('runs role admin markup view cutover in read only mode', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-view-cutover-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/apply-role-admin-markup-view-cutover.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-markup-view-cutover.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-markup-view-cutover.log');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-markup-view-cutover.log');
    assertStringContainsString('MODE read-only', $log);
});
