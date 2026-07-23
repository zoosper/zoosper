<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;
    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'app')) {
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

it('documents the post role admin migration roadmap', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/post-1.38-roadmap.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/post-1.38-roadmap.md'));
    assertStringContainsString('Phase 1.39', $contents);
    assertStringContainsString('rate limiting', $contents);
    assertStringContainsString('RoleAdminController', $contents);
});

it('records the role admin module extraction decision', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/architecture/adr-role-admin-module-extraction.md'));
    $contents = (string) file_get_contents($rootPath('docs/architecture/adr-role-admin-module-extraction.md'));
    assertStringContainsString('zoosper-admin-roles', $contents);
    assertStringContainsString('Do not extract', $contents);
});

it('plans permission tree UX enhancement without changing current behaviour', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/permission-tree-ux-plan.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/permission-tree-ux-plan.md'));
    assertStringContainsString('jsTree', $contents);
    assertStringContainsString('permission_ids[]', $contents);
    assertStringContainsString('progressive enhancement', $contents);
});
