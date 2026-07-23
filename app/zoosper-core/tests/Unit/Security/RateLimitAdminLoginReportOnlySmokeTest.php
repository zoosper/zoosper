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

it('documents admin login report-only smoke', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-admin-login-report-only-smoke.md'));
    assertFileExists($rootPath('docs/development/rate-limit-admin-login-pre-live-hook-closeout.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-admin-login-report-only-smoke.md'));
    assertStringContainsString('admin.login', $contents);
    assertStringContainsString('smoke', $contents);
});

it('provides smoke and smoke readiness tools', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/smoke-rate-limit-admin-login-report-only.php'));
    assertFileExists($rootPath('tools/audit-rate-limit-admin-login-smoke.php'));
});

it('runs the smoke readiness audit', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-smoke-readiness-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-admin-login-smoke.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-smoke-readiness.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-smoke-readiness.log');
    assertStringContainsString('ADMIN_LOGIN_SMOKE_READINESS_ERRORS 0', $log);
});
