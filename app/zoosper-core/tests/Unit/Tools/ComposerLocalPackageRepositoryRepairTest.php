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

it('documents composer local package repository repair', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/composer-local-package-repository-repair.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/composer-local-package-repository-repair.md'));
    assertStringContainsString('zoosper/core', $contents);
    assertStringContainsString('path repositories', $contents);
});

it('provides composer local package audit and repair tools', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-composer-local-packages.php'));
    assertFileExists($rootPath('tools/apply-composer-local-package-repositories.php'));

    $repair = (string) file_get_contents($rootPath('tools/apply-composer-local-package-repositories.php'));
    assertStringContainsString('--apply', $repair);
    assertStringContainsString('phase-1.39-composer-path.bak', $repair);
});

it('runs composer local package audit in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-composer-audit-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-composer-local-packages.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'composer-local-packages.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'composer-local-packages.log');
});
