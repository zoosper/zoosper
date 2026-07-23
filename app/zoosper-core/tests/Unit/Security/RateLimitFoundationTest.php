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

it('documents the rate limiting foundation', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limiting-foundation.md'));
    assertFileExists($rootPath('docs/architecture/adr-database-backed-rate-limiting.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limiting-foundation.md'));
    assertStringContainsString('RateLimitRule', $contents);
    assertStringContainsString('RateLimitDecision', $contents);
    assertStringContainsString('database-backed', $contents);
});

it('provides core rate limit contracts', function () use ($rootPath): void {
    assertFileExists($rootPath('app/zoosper-core/src/Security/RateLimit/RateLimitDecision.php'));
    assertFileExists($rootPath('app/zoosper-core/src/Security/RateLimit/RateLimitRule.php'));
    assertFileExists($rootPath('app/zoosper-core/src/Security/RateLimit/RateLimitStoreInterface.php'));

    $interface = (string) file_get_contents($rootPath('app/zoosper-core/src/Security/RateLimit/RateLimitStoreInterface.php'));
    assertStringContainsString('recordAttempt', $interface);
    assertStringContainsString('reset', $interface);
});

it('runs the rate limit foundation audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-rate-limit-foundation.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-foundation-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-foundation.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-foundation.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-foundation.log');
    assertStringContainsString('RATE_LIMIT_FOUNDATION_ERRORS 0', $log);
});
