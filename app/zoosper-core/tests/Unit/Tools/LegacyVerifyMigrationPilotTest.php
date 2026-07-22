<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertIsArray;
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

$pilotVerifyScripts = [
    'tools/verify-project-structure.php' => 'migrated',
    'tools/verify-runtime-path-safety.php' => 'migrated',
    'tools/verify-service-provider-manifest-file.php' => 'source-owned',
    'tools/verify-module-composer-manifests.php' => 'source-owned',
    'tools/verify-roadmap-planning-docs.php' => 'source-owned',
];

it('documents the first legacy verify Pest migration pilot batch', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/legacy-verify-pest-migration-pilot.md'));
});

it('keeps source-owned pilot scripts present and migrated scripts absent', function () use ($rootPath, $pilotVerifyScripts): void {
    foreach ($pilotVerifyScripts as $script => $status) {
        if ($status === 'migrated') {
            assertFileDoesNotExist($rootPath($script));
            continue;
        }

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

it('keeps the pilot candidate list explicit and reviewable', function () use ($pilotVerifyScripts): void {
    assertIsArray($pilotVerifyScripts);
    assertTrue(count($pilotVerifyScripts) === 5);
});
