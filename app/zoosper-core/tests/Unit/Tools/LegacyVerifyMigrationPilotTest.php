<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;

    while ($current !== dirname($current)) {
        if (
            is_file($current . DIRECTORY_SEPARATOR . 'composer.json')
            && is_dir($current . DIRECTORY_SEPARATOR . 'app')
            && is_dir($current . DIRECTORY_SEPARATOR . 'tools')
        ) {
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

$pilotVerifyScripts = [
    'tools/verify-project-structure.php',
    'tools/verify-runtime-path-safety.php',
    'tools/verify-service-provider-manifest-file.php',
    'tools/verify-module-composer-manifests.php',
    'tools/verify-roadmap-planning-docs.php',
];

it('documents the first legacy verify Pest migration pilot batch', function () use ($rootPath, $pilotVerifyScripts): void {
    $docPath = $rootPath('docs/development/legacy-verify-pest-migration-pilot.md');

    assertFileExists($docPath);

    $contents = (string) file_get_contents($docPath);

    assertStringContainsString('Phase 1.37w.2', $contents);
    assertStringContainsString('A legacy verify script may be removed only after equivalent Pest coverage exists', $contents);

    foreach ($pilotVerifyScripts as $script) {
        assertStringContainsString($script, $contents);
    }
});

it('keeps the pilot batch source-owned until equivalent Pest coverage is migrated', function () use ($rootPath, $pilotVerifyScripts): void {
    foreach ($pilotVerifyScripts as $script) {
        assertFileExists($rootPath($script));
        assertTrue(str_starts_with(basename($script), 'verify-'));
    }
});

it('keeps operational tool prefixes out of the pilot deletion path', function () use ($rootPath): void {
    $contents = (string) file_get_contents($rootPath('docs/development/legacy-verify-pest-migration-pilot.md'));

    foreach (['audit-*', 'diagnose-*', 'inspect-*', 'repair-*', 'smoke-*', 'sync-*', 'publish-*', 'generate-*', 'normalise-*', 'ensure-*'] as $prefix) {
        assertStringContainsString($prefix, $contents);
    }
});

it('can cross-check the generated inventory when present without requiring generated reports in git', function () use ($rootPath, $pilotVerifyScripts): void {
    $inventoryPath = $rootPath('var/reports/tools-inventory.txt');

    if (! is_file($inventoryPath)) {
        assertTrue(true);

        return;
    }

    $contents = (string) file_get_contents($inventoryPath);

    assertStringContainsString('MIGRATE_TO_PEST', $contents);
    assertStringContainsString('KEEP_OPS', $contents);

    foreach ($pilotVerifyScripts as $script) {
        assertStringContainsString($script, $contents);
    }
});

it('keeps the pilot candidate list explicit and reviewable', function () use ($pilotVerifyScripts): void {
    assertIsArray($pilotVerifyScripts);
    assertTrue(count($pilotVerifyScripts) === 5);
});
