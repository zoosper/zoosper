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

it('documents config source discovery', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/config-layering-source-discovery.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/config-layering-source-discovery.md'));
    assertStringContainsString('High-risk config types', $contents);
    assertStringContainsString('routes', $contents);
});

it('provides config source discovery and migration planning tools', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-config-sources.php'));
    assertFileExists($rootPath('tools/plan-config-layering-first-migration.php'));
    assertFileExists($rootPath('tools/audit-config-layering-migration-readiness.php'));
});

it('runs config layering migration readiness audit', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-config-readiness-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-config-layering-migration-readiness.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'config-layering-migration-readiness.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'config-layering-migration-readiness.log');
    assertStringContainsString('CONFIG_LAYERING_MIGRATION_READINESS_ERRORS 0', $log);
});
