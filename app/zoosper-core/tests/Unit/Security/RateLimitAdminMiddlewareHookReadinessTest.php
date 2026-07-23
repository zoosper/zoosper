<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
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

it('documents the admin middleware rate limit hook strategy', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-admin-middleware-hook-strategy.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-admin-middleware-hook-strategy.md'));
    assertStringContainsString('app/zoosper-auth/config/admin_middleware.php', $contents);
    assertStringContainsString('disabled', $contents);
});

it('provides admin middleware hook discovery and planning tools', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/discover-rate-limit-admin-middleware-hook.php'));
    assertFileExists($rootPath('tools/plan-rate-limit-admin-middleware-hook.php'));
    assertFileExists($rootPath('tools/audit-rate-limit-admin-hook-readiness.php'));
});

it('runs the admin hook readiness audit', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-hook-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-admin-hook-readiness.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-admin-hook-readiness.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-admin-hook-readiness.log');
    assertStringContainsString('RATE_LIMIT_ADMIN_HOOK_READINESS_ERRORS 0', $log);
});
