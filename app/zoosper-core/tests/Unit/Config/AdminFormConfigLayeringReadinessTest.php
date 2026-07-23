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

it('documents admin form config layering migration readiness', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/admin-form-config-layering-migration-plan.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/admin-form-config-layering-migration-plan.md'));
    assertStringContainsString('AdminFormUiConfigLoader', $contents);
    assertStringContainsString('ConfigFileLayeredLoader', $contents);
});

it('provides admin form config layering discovery and planner tools', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/discover-admin-form-config-loader.php'));
    assertFileExists($rootPath('tools/plan-admin-form-config-layered-loader.php'));
    assertFileExists($rootPath('tools/audit-admin-form-config-layering-readiness.php'));
});

it('runs admin form config layering readiness audit', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-admin-form-config-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-admin-form-config-layering-readiness.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'admin-form-config-layering-readiness.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'admin-form-config-layering-readiness.log');
    assertStringContainsString('ADMIN_FORM_CONFIG_LAYERING_READINESS_ERRORS 0', $log);
});
