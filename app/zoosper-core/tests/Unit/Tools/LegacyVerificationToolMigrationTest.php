<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertGreaterThanOrEqual;
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

it('documents the legacy verification tool migration policy', function () use ($rootPath): void {
    $policyPath = $rootPath('docs/development/verification-tool-migration.md');

    assertFileExists($policyPath);

    $contents = (string) file_get_contents($policyPath);

    assertStringContainsString('MIGRATE_TO_PEST', $contents);
    assertStringContainsString('KEEP_OPS', $contents);
    assertStringContainsString('DELETE_NOW', $contents);
    assertStringContainsString('REVIEW', $contents);
    assertStringContainsString('verify-*', $contents);
});

it('keeps verification migration separate from operational tools', function () use ($rootPath): void {
    $contents = (string) file_get_contents($rootPath('docs/development/verification-tool-migration.md'));

    foreach (['audit-*', 'diagnose-*', 'inspect-*', 'repair-*', 'smoke-*', 'clean-*', 'publish-*', 'sync-*', 'generate-*', 'normalise-*', 'ensure-*'] as $prefix) {
        assertStringContainsString($prefix, $contents);
    }
});

it('can discover legacy verify scripts as Pest migration candidates without deleting them', function () use ($rootPath): void {
    $legacyVerifyScripts = glob($rootPath('tools/verify-*.php')) ?: [];

    assertIsArray($legacyVerifyScripts);
    assertGreaterThanOrEqual(1, count($legacyVerifyScripts));

    foreach ($legacyVerifyScripts as $scriptPath) {
        assertTrue(str_starts_with(basename($scriptPath), 'verify-'));
    }
});

it('recognises generated tools inventory reports when they are present', function () use ($rootPath): void {
    $inventoryPath = $rootPath('var/reports/tools-inventory.txt');

    if (! is_file($inventoryPath)) {
        assertTrue(true);

        return;
    }

    $contents = (string) file_get_contents($inventoryPath);

    assertStringContainsString('MIGRATE_TO_PEST', $contents);
    assertStringContainsString('KEEP_OPS', $contents);
    assertStringContainsString('DELETE_NOW', $contents);
    assertStringContainsString('REVIEW', $contents);
});
