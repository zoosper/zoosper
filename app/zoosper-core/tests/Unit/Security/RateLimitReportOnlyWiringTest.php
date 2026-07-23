<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\InMemoryRateLimitReportSink;
use Zoosper\Core\Security\RateLimit\RateLimitDecision;
use Zoosper\Core\Security\RateLimit\RateLimitMiddlewareIntegration;
use Zoosper\Core\Security\RateLimit\RateLimitReportOnlyMiddlewareFactory;
use Zoosper\Core\Security\RateLimit\RateLimitRule;
use Zoosper\Core\Security\RateLimit\RateLimitRuntimeConfig;
use Zoosper\Core\Security\RateLimit\RateLimitStoreInterface;
use Zoosper\Core\Security\RateLimit\ReportOnlyRateLimitMiddleware;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertInstanceOf;
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

$factory = static function (): RateLimitReportOnlyMiddlewareFactory {
    $store = new class implements RateLimitStoreInterface {
        public function recordAttempt(RateLimitRule $rule, string $identityHash, int $now): RateLimitDecision
        {
            return RateLimitDecision::allow(1, $rule->maxAttempts);
        }

        public function reset(RateLimitRule $rule, string $identityHash): void
        {
        }
    };

    return new RateLimitReportOnlyMiddlewareFactory($store, new InMemoryRateLimitReportSink());
};

it('documents disabled by default report only wiring', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-report-only-wiring-strategy.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-report-only-wiring-strategy.md'));
    assertStringContainsString('disabled-by-default', $contents);
    assertStringContainsString('ReportOnlyRateLimitMiddleware', $contents);
});

it('returns no middleware when runtime config is disabled', function () use ($factory): void {
    $integration = new RateLimitMiddlewareIntegration($factory());
    $config = RateLimitRuntimeConfig::fromArray(['enabled' => false, 'mode' => 'report_only']);

    assertSame([], $integration->middleware($config));
});

it('returns report-only middleware when explicitly enabled in report-only mode', function () use ($factory): void {
    $integration = new RateLimitMiddlewareIntegration($factory());
    $config = RateLimitRuntimeConfig::fromArray([
        'enabled' => true,
        'mode' => 'report_only',
        'policies' => [
            'admin.login' => ['scope' => 'admin', 'max_attempts' => 5, 'window_seconds' => 300],
        ],
    ]);

    $middleware = $integration->middleware($config);

    assertCount(1, $middleware);
    assertInstanceOf(ReportOnlyRateLimitMiddleware::class, $middleware[0]);
});

it('returns no middleware for enforce mode in this phase', function () use ($factory): void {
    $integration = new RateLimitMiddlewareIntegration($factory());
    $config = RateLimitRuntimeConfig::fromArray(['enabled' => true, 'mode' => 'enforce']);

    assertSame([], $integration->middleware($config));
});

it('runs the report-only wiring audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-rate-limit-report-only-wiring.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-wire-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-report-only-wiring.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-report-only-wiring.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-report-only-wiring.log');
    assertStringContainsString('RATE_LIMIT_REPORT_ONLY_WIRING_ERRORS 0', $log);
});
