<?php

declare(strict_types=1);

use Zoosper\Auth\Http\RateLimitReportOnlyAdminMiddleware;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

function rateLimitMiddlewareTestPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function rateLimitMiddlewareTestBasePath(array $rateLimitConfig): string
{
    $tmp = sys_get_temp_dir() . '/zoosper-rate-limit-mw-' . bin2hex(random_bytes(6));
    mkdir($tmp . '/app/zoosper-core/config', 0775, true);
    file_put_contents($tmp . '/app/zoosper-core/config/rate_limit.php', "<?php\nreturn " . var_export($rateLimitConfig, true) . ";\n");
    return $tmp;
}

function rateLimitMiddlewareTestLoginRequest(string $email = 'admin@example.test', string $ip = '203.0.113.10'): Request
{
    $_POST = ['email' => $email, 'password' => 'irrelevant'];
    return new Request(method: 'POST', path: '/admin/login', clientIp: $ip);
}

it('does not touch the request at all when rate limiting is disabled', function (): void {
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => false,
        'mode' => 'report_only',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => '',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 5, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $nextCalled = false;
    $response = $middleware->process(rateLimitMiddlewareTestLoginRequest(), new RouteContext('POST', '/admin/login'), function () use (&$nextCalled): Response {
        $nextCalled = true;
        return Response::html('ok');
    });

    expect($nextCalled)->toBeTrue();
    expect($response)->toBeInstanceOf(Response::class);
    expect(is_file($basePath . '/var/reports/rate-limit-events.jsonl'))->toBeFalse();
});

it('never blocks the request even after the policy limit is exceeded and records report events', function (): void {
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => true,
        'mode' => 'report_only',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => 'test-salt',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 2, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $context = new RouteContext('POST', '/admin/login');
    $next = static fn (): Response => Response::html('ok');

    $middleware->process(rateLimitMiddlewareTestLoginRequest(), $context, $next);
    $middleware->process(rateLimitMiddlewareTestLoginRequest(), $context, $next);
    $response = $middleware->process(rateLimitMiddlewareTestLoginRequest(), $context, $next);

    expect($response)->toBeInstanceOf(Response::class);

    $lines = array_values(array_filter(explode("\n", (string) file_get_contents($basePath . '/var/reports/rate-limit-events.jsonl'))));
    expect(count($lines))->toBe(3);

    $lastEvent = json_decode($lines[2], true);
    expect($lastEvent['allowed'])->toBeFalse();
    expect($lastEvent['attempts'])->toBe(3);
    expect($lastEvent['max_attempts'])->toBe(2);
});

it('passes through untouched for any path other than the admin login POST', function (): void {
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => true,
        'mode' => 'report_only',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => '',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 1, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $nextCalled = false;
    $middleware->process(new Request(method: 'GET', path: '/admin/pages', clientIp: '203.0.113.10'), new RouteContext('GET', '/admin/pages'), function () use (&$nextCalled): Response {
        $nextCalled = true;
        return Response::html('ok');
    });

    expect($nextCalled)->toBeTrue();
    expect(is_file($basePath . '/var/reports/rate-limit-events.jsonl'))->toBeFalse();
});

it('does not touch the request when enabled but mode is enforce', function (): void {
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => true,
        'mode' => 'enforce',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => '',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 1, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $nextCalled = false;
    $middleware->process(rateLimitMiddlewareTestLoginRequest(), new RouteContext('POST', '/admin/login'), function () use (&$nextCalled): Response {
        $nextCalled = true;
        return Response::html('ok');
    });

    expect($nextCalled)->toBeTrue();
});
