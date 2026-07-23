<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\InMemoryRateLimitReportSink;
use Zoosper\Core\Security\RateLimit\RateLimitContext;
use Zoosper\Core\Security\RateLimit\RateLimitDecision;
use Zoosper\Core\Security\RateLimit\RateLimitEnforcer;
use Zoosper\Core\Security\RateLimit\RateLimitGuard;
use Zoosper\Core\Security\RateLimit\RateLimitRule;
use Zoosper\Core\Security\RateLimit\RateLimitStoreInterface;
use Zoosper\Core\Security\RateLimit\ReportOnlyRateLimitMiddleware;
use Zoosper\Core\Security\RateLimit\StaticRateLimitPolicyResolver;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertFileExists;
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

it('documents the report only rate limit middleware adapter', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-report-only-middleware.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-report-only-middleware.md'));
    assertStringContainsString('ReportOnlyRateLimitMiddleware', $contents);
    assertStringContainsString('non-enforcing', $contents);
});

it('reports denied decisions but still executes downstream', function (): void {
    $rule = new RateLimitRule('admin.login', 1, 60, 'admin');
    $store = new class implements RateLimitStoreInterface {
        public function recordAttempt(RateLimitRule $rule, string $identityHash, int $now): RateLimitDecision
        {
            return RateLimitDecision::deny(2, $rule->maxAttempts, 30);
        }
        public function reset(RateLimitRule $rule, string $identityHash): void
        {
        }
    };

    $guard = new RateLimitGuard(
        new StaticRateLimitPolicyResolver(['admin.login' => $rule]),
        new RateLimitEnforcer($store),
    );
    $sink = new InMemoryRateLimitReportSink();
    $middleware = new ReportOnlyRateLimitMiddleware($guard, $sink);

    $called = false;
    $result = $middleware->handle(new RateLimitContext('admin.login', 'identity-hash', 100), function (RateLimitDecision $decision) use (&$called): string {
        $called = true;
        assertFalse($decision->allowed);
        return 'downstream-ran';
    });

    assertSame('downstream-ran', $result);
    assertTrue($called);
    assertCount(1, $sink->events());
    assertFalse($sink->events()[0]->allowed);
    assertSame('admin.login', $sink->events()[0]->key);
});

it('records allowed decisions as report events', function (): void {
    $rule = new RateLimitRule('admin.login', 3, 60, 'admin');
    $store = new class implements RateLimitStoreInterface {
        public function recordAttempt(RateLimitRule $rule, string $identityHash, int $now): RateLimitDecision
        {
            return RateLimitDecision::allow(1, $rule->maxAttempts);
        }
        public function reset(RateLimitRule $rule, string $identityHash): void
        {
        }
    };

    $sink = new InMemoryRateLimitReportSink();
    $middleware = new ReportOnlyRateLimitMiddleware(
        new RateLimitGuard(new StaticRateLimitPolicyResolver(['admin.login' => $rule]), new RateLimitEnforcer($store)),
        $sink,
    );

    $middleware->handle(new RateLimitContext('admin.login', 'identity-hash', 100), fn () => 'ok');

    assertCount(1, $sink->events());
    assertTrue($sink->events()[0]->allowed);
    assertSame(1, $sink->events()[0]->attempts);
});

it('runs the report only middleware audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-rate-limit-report-only-middleware.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-report-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-report-only-middleware.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-report-only-middleware.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-report-only-middleware.log');
    assertStringContainsString('RATE_LIMIT_REPORT_ONLY_MIDDLEWARE_ERRORS 0', $log);
});
