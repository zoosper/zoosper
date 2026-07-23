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

it('documents phase 1.39 rate limit closeout', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-phase-139-closeout.md'));
    assertFileExists($rootPath('docs/architecture/adr-rate-limiting-report-only-to-enforcement.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-phase-139-closeout.md'));
    assertStringContainsString('Phase 1.39', $contents);
    assertStringContainsString('disabled', $contents);
});

it('provides live hook and closeout audit tools', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/apply-rate-limit-admin-middleware-hook.php'));
    assertFileExists($rootPath('tools/audit-rate-limit-admin-middleware-hook.php'));
    assertFileExists($rootPath('tools/audit-rate-limit-phase-139-closeout.php'));
});
