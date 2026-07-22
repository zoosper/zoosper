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

it('adds role admin latte scaffold templates', function () use ($rootPath): void {
    assertFileExists($rootPath('app/zoosper-core/views/admin/roles/index.latte'));
    assertFileExists($rootPath('app/zoosper-core/views/admin/roles/form.latte'));

    assertStringContainsString('Roles', (string) file_get_contents($rootPath('app/zoosper-core/views/admin/roles/index.latte')));
    assertStringContainsString('csrf_token', (string) file_get_contents($rootPath('app/zoosper-core/views/admin/roles/form.latte')));
});

it('documents the role admin template scaffold contract', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-template-scaffold.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-template-scaffold.md'));

    assertStringContainsString('index.latte', $contents);
    assertStringContainsString('form.latte', $contents);
    assertStringContainsString('Template data assumptions', $contents);
});

it('provides a read only role admin template scaffold audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-role-admin-template-scaffold.php'));

    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-template-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-role-admin-template-scaffold.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-template-scaffold.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-template-scaffold.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-template-scaffold.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-template-scaffold.log');

    assertStringContainsString('# Role Admin Template Scaffold Audit', $report);
    assertStringContainsString('Errors: 0', $report);
    assertStringContainsString('SCAFFOLD_ERRORS 0', $log);
});
