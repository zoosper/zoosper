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
use function PHPUnit\Framework\assertStringContainsString;

it('records all pilot scripts as migrated and absent', function () use ($rootPath): void {
    $status = (string) file_get_contents($rootPath('docs/development/legacy-verify-migration-status.md'));
    foreach ([
        'tools/verify-project-structure.php',
        'tools/verify-runtime-path-safety.php',
        'tools/verify-service-provider-manifest-file.php',
        'tools/verify-module-composer-manifests.php',
        'tools/verify-roadmap-planning-docs.php',
    ] as $script) {
        assertStringContainsString('| `' . $script . '` | migrated |', $status);
        assertFileDoesNotExist($rootPath($script));
    }
});
