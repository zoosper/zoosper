<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertDirectoryExists;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;

    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'tools')) {
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

it('keeps module composer manifest legacy script source owned before ledger promotion', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/verify-module-composer-manifests.php'));
    assertFileExists($rootPath('docs/development/legacy-verify-migration-status.md'));

    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));

    assertStringContainsString('| `tools/verify-module-composer-manifests.php` | source-owned |', $status);
});

it('keeps root composer json readable with autoload metadata', function () use ($rootPath): void {
    assertFileExists($rootPath('composer.json'));

    $composer = json_decode((string) file_get_contents($rootPath('composer.json')), true);

    assertIsArray($composer);
    assertStringContainsString('autoload', json_encode($composer, JSON_THROW_ON_ERROR));
    assertStringContainsString('autoload-dev', json_encode($composer, JSON_THROW_ON_ERROR));
});

it('keeps media package composer manifest readable with package metadata', function () use ($rootPath): void {
    assertFileExists($rootPath('packages/zoosper-media/composer.json'));

    $composer = json_decode((string) file_get_contents($rootPath('packages/zoosper-media/composer.json')), true);

    assertIsArray($composer);
    assertStringContainsString('autoload', json_encode($composer, JSON_THROW_ON_ERROR));
});

it('keeps package/module composer workflow signals discoverable in current source', function () use ($rootPath): void {
    assertDirectoryExists($rootPath('packages/zoosper-media'));
    assertFileExists($rootPath('tools/audit-media-standalone-package.php'));
    assertFileExists($rootPath('app/zoosper-core/tests/Unit/Composer/ModuleComposerManifestGeneratorTest.php'));
    assertFileExists($rootPath('app/zoosper-core/tests/Unit/Composer/ModulePackageIdentityTest.php'));
    assertFileExists($rootPath('app/zoosper-core/tests/Unit/Composer/PackageTestsuiteConfigurationTest.php'));
});

it('keeps controlled removal protections available for module composer manifests candidate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/remove-migrated-legacy-verify.php'));

    $source = (string) file_get_contents($rootPath('tools/remove-migrated-legacy-verify.php'));

    assertStringContainsString('tools/verify-module-composer-manifests.php', $source);
    assertStringContainsString('migrationStatusFor', $source);
    assertStringContainsString('allowedPilotScripts', $source);
});

it('provides read only evidence tooling for the module composer manifests migration candidate', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-verify-module-composer-manifests-migration.php'));
    assertFileExists($rootPath('docs/development/verify-module-composer-manifests-migration.md'));

    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-module-composer-migration-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-verify-module-composer-manifests-migration.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'verify-module-composer-manifests-migration.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'verify-module-composer-manifests-migration.log');

    $report = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'verify-module-composer-manifests-migration.txt');

    assertStringContainsString('Legacy script: tools/verify-module-composer-manifests.php', $report);
    assertStringContainsString('Migration status: source-owned', $report);
    assertStringContainsString('Errors: 0', $report);
});

it('still refuses deletion while module composer manifests remains source owned', function () use ($rootPath): void {
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/remove-migrated-legacy-verify.php'))
        . ' --script=tools/verify-module-composer-manifests.php --apply --confirm-pest-coverage --confirm-remove';

    exec($command, $output, $exitCode);

    assertTrue($exitCode !== 0);
    assertFileExists($rootPath('tools/verify-module-composer-manifests.php'));
});
