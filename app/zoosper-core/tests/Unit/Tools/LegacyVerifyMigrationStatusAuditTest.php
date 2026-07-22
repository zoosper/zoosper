<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;
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

it('documents migrated and source owned legacy verify statuses', function () use ($rootPath): void {
    $contents = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));
    assertStringContainsString('| `tools/verify-service-provider-manifest-file.php` | migrated |', $contents);
    assertStringContainsString('| `tools/verify-module-composer-manifests.php` | source-owned |', $contents);
});

it('allows migrated scripts to be absent while source-owned scripts remain present', function () use ($rootPath): void {
    foreach (['tools/verify-project-structure.php', 'tools/verify-runtime-path-safety.php', 'tools/verify-service-provider-manifest-file.php'] as $script) {
        assertFileDoesNotExist($rootPath($script));
    }
    foreach (['tools/verify-module-composer-manifests.php', 'tools/verify-roadmap-planning-docs.php'] as $script) {
        assertFileExists($rootPath($script));
    }
});
