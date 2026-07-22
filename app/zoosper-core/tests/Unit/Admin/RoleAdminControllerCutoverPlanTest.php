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

it('documents the role admin controller cutover plan', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-controller-cutover.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-controller-cutover.md'));

    assertStringContainsString('RoleAdminController', $contents);
    assertStringContainsString('admin/roles/index.latte', $contents);
    assertStringContainsString('admin/roles/form.latte', $contents);
    assertStringContainsString('CSRF', $contents);
});

it('provides a read only role admin controller cutover planner', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/plan-role-admin-controller-cutover.php'));

    $source = (string) file_get_contents($rootPath('tools/plan-role-admin-controller-cutover.php'));

    assertStringContainsString('Read-only cutover planner', $source);
    assertStringContainsString('role-admin-controller-cutover.txt', $source);
    assertStringContainsString('discoverRenderSignals', $source);
});

it('generates a role admin controller cutover plan in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-cutover-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/plan-role-admin-controller-cutover.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-controller-cutover.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-controller-cutover.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-controller-cutover.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-controller-cutover.log');

    assertStringContainsString('# RoleAdminController Cutover Plan', $report);
    assertStringContainsString('Recommended cutover steps', $report);
    assertStringContainsString('CONTROLLER_FOUND yes', $log);
    assertStringContainsString('CUTOVER_ERRORS 0', $log);
});
