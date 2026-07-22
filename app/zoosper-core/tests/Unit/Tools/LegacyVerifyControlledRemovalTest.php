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
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertStringContainsString;

it('keeps controlled removal tooling after pilot closeout', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/remove-migrated-legacy-verify.php'));
    $source = (string) file_get_contents($rootPath('tools/remove-migrated-legacy-verify.php'));
    assertStringContainsString('migrationStatusFor', $source);
});

it('records all pilot legacy verify scripts as retired', function () use ($rootPath): void {
    foreach ([
        'tools/verify-project-structure.php',
        'tools/verify-runtime-path-safety.php',
        'tools/verify-service-provider-manifest-file.php',
        'tools/verify-module-composer-manifests.php',
        'tools/verify-roadmap-planning-docs.php',
    ] as $script) {
        assertFileDoesNotExist($rootPath($script));
    }
});
