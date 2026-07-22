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

it('records four migrated pilot scripts as removed and final candidate source owned', function () use ($rootPath): void {
    foreach (['tools/verify-project-structure.php','tools/verify-runtime-path-safety.php','tools/verify-service-provider-manifest-file.php','tools/verify-module-composer-manifests.php'] as $script) {
        assertFileDoesNotExist($rootPath($script));
    }
    assertFileExists($rootPath('tools/verify-roadmap-planning-docs.php'));
    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));
    assertStringContainsString('| `tools/verify-module-composer-manifests.php` | migrated |', $status);
    assertStringContainsString('| `tools/verify-roadmap-planning-docs.php` | source-owned |', $status);
});
