<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertDirectoryExists;
use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;
    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'tools')) return $current;
        $current = dirname($current);
    }
    fail('Unable to locate Zoosper repository root from ' . __DIR__);
};
$rootPath = static fn (string $path = ''): string => ($r = $repoRootPath()) && $path === '' ? $r : $r . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);

it('records module composer manifests legacy script as migrated after retirement', function () use ($rootPath): void {
    assertFileDoesNotExist($rootPath('tools/verify-module-composer-manifests.php'));
    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));
    assertStringContainsString('| `tools/verify-module-composer-manifests.php` | migrated |', $status);
});

it('keeps root and media composer manifests readable', function () use ($rootPath): void {
    foreach (['composer.json', 'packages/zoosper-media/composer.json'] as $file) {
        assertFileExists($rootPath($file));
        $composer = json_decode((string) file_get_contents($rootPath($file)), true);
        assertIsArray($composer);
        assertStringContainsString('autoload', json_encode($composer, JSON_THROW_ON_ERROR));
    }
});

it('keeps package/module composer workflow signals discoverable in current source', function () use ($rootPath): void {
    assertDirectoryExists($rootPath('packages/zoosper-media'));
    assertFileExists($rootPath('tools/audit-media-standalone-package.php'));
    assertFileExists($rootPath('app/zoosper-core/tests/Unit/Composer/ModuleComposerManifestGeneratorTest.php'));
    assertFileExists($rootPath('app/zoosper-core/tests/Unit/Composer/ModulePackageIdentityTest.php'));
});

it('provides read only evidence tooling for the module composer manifests migration candidate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-verify-module-composer-manifests-migration.php'));
    assertFileExists($rootPath('docs/development/verify-module-composer-manifests-migration.md'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-module-composer-migration-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rootPath('tools/audit-verify-module-composer-manifests-migration.php')) . ' --output-dir=' . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'verify-module-composer-manifests-migration.txt');
    assertStringContainsString('Legacy script: tools/verify-module-composer-manifests.php', $report);
    assertStringContainsString('Migration status: migrated', $report);
    assertStringContainsString('Errors: 0', $report);
});
