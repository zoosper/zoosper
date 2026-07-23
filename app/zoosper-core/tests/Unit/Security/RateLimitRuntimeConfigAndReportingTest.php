<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\FileRateLimitReportSink;
use Zoosper\Core\Security\RateLimit\RateLimitDecision;
use Zoosper\Core\Security\RateLimit\RateLimitReportEvent;
use Zoosper\Core\Security\RateLimit\RateLimitContext;
use Zoosper\Core\Security\RateLimit\RateLimitRuntimeConfig;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;
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

it('provides disabled by default rate limit config', function () use ($rootPath): void {
    assertFileExists($rootPath('app/zoosper-core/config/rate_limit.php'));
    $config = require $rootPath('app/zoosper-core/config/rate_limit.php');
    assertIsArray($config);
    assertFalse($config['enabled']);
    assertSame('report_only', $config['mode']);
});

it('parses runtime config policies into rules', function (): void {
    $config = RateLimitRuntimeConfig::fromArray([
        'enabled' => true,
        'mode' => 'report_only',
        'report_path' => 'var/test.jsonl',
        'identity_salt' => 'salt',
        'policies' => [
            'admin.login' => ['scope' => 'admin', 'max_attempts' => 3, 'window_seconds' => 60],
        ],
    ]);

    assertTrue($config->enabled);
    assertTrue($config->isReportOnly());
    assertFalse($config->isEnforcing());
    assertSame(3, $config->policies['admin.login']->maxAttempts);
});

it('writes report events using the file report sink', function (): void {
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-limit-' . bin2hex(random_bytes(6)) . '.jsonl';
    $sink = new FileRateLimitReportSink($path);
    $sink->record(RateLimitReportEvent::fromDecision(
        new RateLimitContext('admin.login', 'identity-hash', 100),
        RateLimitDecision::deny(2, 1, 30),
    ));

    assertFileExists($path);
    $contents = (string) file_get_contents($path);
    assertStringContainsString('admin.login', $contents);
    assertStringContainsString('identity-hash', $contents);
});

it('runs the runtime config audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-rate-limit-runtime-config.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-config-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-runtime-config.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-runtime-config.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-runtime-config.log');
    assertStringContainsString('RATE_LIMIT_RUNTIME_CONFIG_ERRORS 0', $log);
});
