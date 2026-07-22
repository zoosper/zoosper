<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertTrue;
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

$pilotVerifyScripts = [
    'tools/verify-project-structure.php' => 'migrated',
    'tools/verify-runtime-path-safety.php' => 'migrated',
    'tools/verify-service-provider-manifest-file.php' => 'migrated',
    'tools/verify-module-composer-manifests.php' => 'source-owned',
    'tools/verify-roadmap-planning-docs.php' => 'source-owned',
];

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

it('keeps the pilot candidate list explicit and reviewable', function () use ($pilotVerifyScripts): void {
    assertIsArray($pilotVerifyScripts);
    assertTrue(count($pilotVerifyScripts) === 5);
});
