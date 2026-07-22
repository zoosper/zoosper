<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertDirectoryExists;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertIsArray;
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

it('preserves the root project structure required by Zoosper tooling', function () use ($rootPath): void {
    foreach (['app', 'packages', 'tools', 'docs', 'public'] as $directory) {
        assertDirectoryExists($rootPath($directory));
    }

    assertFileExists($rootPath('composer.json'));
    assertFileExists($rootPath('phpunit.xml'));
});

it('keeps composer json readable for module and package discovery', function () use ($rootPath): void {
    $composer = json_decode((string) file_get_contents($rootPath('composer.json')), true);

    assertIsArray($composer);
    assertStringContainsString('autoload', json_encode($composer, JSON_THROW_ON_ERROR));
    assertStringContainsString('autoload-dev', json_encode($composer, JSON_THROW_ON_ERROR));
});

it('keeps core module source and test directories discoverable', function () use ($rootPath): void {
    assertDirectoryExists($rootPath('app/zoosper-core'));
    assertDirectoryExists($rootPath('app/zoosper-core/src'));
    assertDirectoryExists($rootPath('app/zoosper-core/tests'));
    assertDirectoryExists($rootPath('app/zoosper-core/tests/Unit'));
});

it('keeps media package source and tests discoverable after package extraction work', function () use ($rootPath): void {
    assertDirectoryExists($rootPath('packages/zoosper-media'));
    assertDirectoryExists($rootPath('packages/zoosper-media/src'));
    assertDirectoryExists($rootPath('packages/zoosper-media/tests'));
});

it('keeps legacy project structure verify script source-owned before ledger promotion', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/verify-project-structure.php'));
    assertFileExists($rootPath('docs/development/legacy-verify-migration-status.md'));

    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));

    assertStringContainsString('| `tools/verify-project-structure.php` | source-owned |', $status);
});

it('provides read only evidence tooling for the project structure migration candidate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-verify-project-structure-migration.php'));
    assertFileExists($rootPath('docs/development/verify-project-structure-migration.md'));

    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-project-structure-migration-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-verify-project-structure-migration.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'verify-project-structure-migration.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'verify-project-structure-migration.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'verify-project-structure-migration.txt');

    assertStringContainsString('Legacy script: tools/verify-project-structure.php', $report);
    assertStringContainsString('Replacement Pest coverage:', $report);
    assertStringContainsString('Errors: 0', $report);
});
