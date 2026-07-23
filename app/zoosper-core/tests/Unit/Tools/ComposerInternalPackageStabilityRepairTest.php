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

it('documents composer internal package stability repair', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/composer-internal-package-stability-repair.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/composer-internal-package-stability-repair.md'));
    assertStringContainsString('minimum-stability', $contents);
    assertStringContainsString('prefer-stable', $contents);
    assertStringContainsString('zoosper/core', $contents);
});

it('provides the composer internal package stability repair tool', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/apply-composer-internal-package-stability.php'));
    $source = (string) file_get_contents($rootPath('tools/apply-composer-internal-package-stability.php'));
    assertStringContainsString('--apply', $source);
    assertStringContainsString('minimum-stability', $source);
    assertStringContainsString('prefer-stable', $source);
});

it('runs composer stability repair in dry-run mode', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-composer-stability-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/apply-composer-internal-package-stability.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'composer-internal-package-stability.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'composer-internal-package-stability.log');
    assertStringContainsString('COMPOSER_STABILITY_REPAIR_ERRORS 0', $log);
});
