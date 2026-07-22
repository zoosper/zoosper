<?php

declare(strict_types=1);

$repoRootPath = static function (): string {
    $current = __DIR__;
    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'tools')) {
            return $current;
        }
        $current = dirname($current);
    }
    \PHPUnit\Framework\fail('Unable to locate Zoosper repository root from ' . __DIR__);
};

$rootPath = static function (string $path = '') use ($repoRootPath): string {
    $root = $repoRootPath();
    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
};

use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertTrue;

$pilotVerifyScripts = [
    'tools/verify-project-structure.php' => 'migrated',
    'tools/verify-runtime-path-safety.php' => 'migrated',
    'tools/verify-service-provider-manifest-file.php' => 'migrated',
    'tools/verify-module-composer-manifests.php' => 'migrated',
    'tools/verify-roadmap-planning-docs.php' => 'migrated',
];

it('keeps the first pilot batch fully migrated', function () use ($rootPath, $pilotVerifyScripts): void {
    foreach ($pilotVerifyScripts as $script => $status) {
        assertTrue($status === 'migrated');
        assertFileDoesNotExist($rootPath($script));
    }
});

it('keeps the pilot candidate list explicit and reviewable', function () use ($pilotVerifyScripts): void {
    assertIsArray($pilotVerifyScripts);
    assertTrue(count($pilotVerifyScripts) === 5);
});
