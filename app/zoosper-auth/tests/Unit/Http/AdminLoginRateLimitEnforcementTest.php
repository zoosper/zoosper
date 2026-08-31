<?php

declare(strict_types=1);

use Zoosper\Auth\Http\RateLimitReportOnlyAdminMiddleware;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

function enforcingRateLimitPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function enforcingRateLimitBase(array $config): string
{
    $base = sys_get_temp_dir() . '/zoosper-rate-enforce-' . bin2hex(random_bytes(4));
    mkdir($base . '/app/zoosper-core/config', 0777, true);
    file_put_contents($base . '/app/zoosper-core/config/rate_limit.php', '<?php return ' . var_export($config, true) . ';');
    return $base;
}

function enforcingLoginRequest(): Request
{
    return new Request('POST', '/admin/login', clientIp: '203.0.113.10', form: ['email' => 'Admin@Example.test']);
}

it('returns a generic 429 with Retry-After and does not execute downstream after denial', function (): void {
    $base = enforcingRateLimitBase([
        'enabled' => true,
        'mode' => 'enforce',
        'report_path' => 'var/reports/rate.jsonl',
        'identity_salt' => str_repeat('a', 64),
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 1, 'window_seconds' => 300]],
    ]);
    $middleware = new RateLimitReportOnlyAdminMiddleware(enforcingRateLimitPdo(), $base);
    $context = new RouteContext('POST', '/admin/login', isPublic: true);
    $calls = 0;
    $next = static function () use (&$calls): Response { $calls++; return Response::html('login'); };

    expect($middleware->process(enforcingLoginRequest(), $context, $next)->statusCode())->toBe(200);
    $denied = $middleware->process(enforcingLoginRequest(), $context, $next);

    expect($denied->statusCode())->toBe(429)
        ->and($denied->headers())->toHaveKey('Retry-After')
        ->and((int) $denied->headers()['Retry-After'])->toBeGreaterThanOrEqual(1)
        ->and($denied->headers()['Cache-Control'])->toBe('no-store')
        ->and($denied->body())->toContain('Too many sign-in attempts')
        ->not->toContain('Admin@Example.test')
        ->and($calls)->toBe(1);
});

it('keeps report-only mode non-blocking after the same policy limit', function (): void {
    $base = enforcingRateLimitBase([
        'enabled' => true,
        'mode' => 'report_only',
        'report_path' => 'var/reports/rate.jsonl',
        'identity_salt' => str_repeat('b', 64),
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 1, 'window_seconds' => 300]],
    ]);
    $middleware = new RateLimitReportOnlyAdminMiddleware(enforcingRateLimitPdo(), $base);
    $context = new RouteContext('POST', '/admin/login', isPublic: true);
    $calls = 0;
    $next = static function () use (&$calls): Response { $calls++; return Response::html('login'); };

    $middleware->process(enforcingLoginRequest(), $context, $next);
    $response = $middleware->process(enforcingLoginRequest(), $context, $next);

    expect($response->statusCode())->toBe(200)
        ->and($calls)->toBe(2);
});










