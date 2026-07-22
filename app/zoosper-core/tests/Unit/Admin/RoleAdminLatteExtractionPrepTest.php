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

it('documents the role admin template extraction contract', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-template-contract.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-template-contract.md'));

    assertStringContainsString('RoleAdminController', $contents);
    assertStringContainsString('index.latte', $contents);
    assertStringContainsString('form.latte', $contents);
    assertStringContainsString('CSRF', $contents);
});

it('provides a read only role admin extraction planner', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/plan-role-admin-latte-extraction.php'));

    $source = (string) file_get_contents($rootPath('tools/plan-role-admin-latte-extraction.php'));

    assertStringContainsString('Read-only planner', $source);
    assertStringContainsString('RoleAdminController.php', $source);
    assertStringContainsString('role-admin-latte-extraction-plan.txt', $source);
});

it('generates a role admin latte extraction plan in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-admin-plan-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/plan-role-admin-latte-extraction.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-extraction-plan.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-extraction-plan.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-extraction-plan.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-latte-extraction-plan.log');

    assertStringContainsString('# RoleAdminController Latte Extraction Plan', $report);
    assertStringContainsString('Suggested template targets', $report);
    assertStringContainsString('CONTROLLER_FOUND yes', $log);
    assertStringContainsString('EXTRACTION_ERRORS 0', $log);
});
