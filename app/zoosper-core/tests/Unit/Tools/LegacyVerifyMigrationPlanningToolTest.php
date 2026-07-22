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

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertStringContainsString;

it('keeps read only migration planning tooling available after pilot closeout', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/plan-legacy-verify-migration.php'));
    $source = (string) file_get_contents($rootPath('tools/plan-legacy-verify-migration.php'));
    assertStringContainsString('This command is read-only', $source);
});
