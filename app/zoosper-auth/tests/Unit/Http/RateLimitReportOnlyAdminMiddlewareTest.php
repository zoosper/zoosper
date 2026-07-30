<?php

declare(strict_types=1);

use Zoosper\Auth\Http\RateLimitReportOnlyAdminMiddleware;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * TEST-FIDELITY FIX (2026-07-30, discovered while adding the identity-salt
 * tests below): rateLimitMiddlewareTestLoginRequest() previously set global
 * $_POST directly, relying on the OLD Request::form() behaviour (reading
 * $_POST live on every call). After the Request::form() immutability fix
 * (same session, earlier phase), form() reads an immutable constructor
 * property instead — so the global $_POST assignment here became dead code
 * with no effect, and RateLimitReportOnlyAdminMiddleware::process() (which
 * calls $request->form()['email']) silently stopped receiving the email
 * value at all.
 *
 * This was NOT caught at the time: the immutability-fix phase's file-by-file
 * ->form() check covered 8 of the 11 files found by the broader "constructs
 * a Request manually" grep, and this file was not among the 8 actually
 * checked. IMPORTANT: this is a TEST-FIDELITY gap, not a live production
 * bug — real requests flow through Request::fromGlobals(), which correctly
 * captures $_POST. The existing test below happened to keep passing anyway,
 * because it used the same email and IP across all of its calls, so the
 * identity silently became IP-only instead of email+IP without any
 * assertion noticing. Fixed here by passing form data through the
 * constructor properly, restoring genuine email+IP identity coverage.
 */
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

    $export = var_export($rateLimitConfig, true);
    file_put_contents($tmp . '/app/zoosper-core/config/rate_limit.php', "<?php\nreturn {$export};\n");

    return $tmp;
}

function rateLimitMiddlewareTestLoginRequest(string $email = 'admin@example.test', string $ip = '203.0.113.10'): Request
{
    return new Request(
        method: 'POST',
        path: '/admin/login',
        clientIp: $ip,
        form: ['email' => $email, 'password' => 'irrelevant'],
    );
}

it('does not touch the request at all when rate limiting is disabled (real default config)', function (): void {
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => false,
        'mode' => 'report_only',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => '',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 5, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $request = rateLimitMiddlewareTestLoginRequest();
    $context = new RouteContext('POST', '/admin/login');

    $nextCalled = false;
    $response = $middleware->process($request, $context, function () use (&$nextCalled): Response {
        $nextCalled = true;
        return Response::html('ok');
    });

    expect($nextCalled)->toBeTrue();
    expect($response)->toBeInstanceOf(Response::class);
    expect(is_file($basePath . '/var/reports/rate-limit-events.jsonl'))->toBeFalse();
});

it('never blocks the request even after the policy limit is exceeded, and records report events', function (): void {
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => true,
        'mode' => 'report_only',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => 'test-salt',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 2, 'window_seconds' => 300]],
    ]);

    $pdo = rateLimitMiddlewareTestPdo();
    $middleware = new RateLimitReportOnlyAdminMiddleware($pdo, $basePath);
    $context = new RouteContext('POST', '/admin/login');
    $next = static fn (): Response => Response::html('ok');

    // Same identity, 3 attempts — exceeds the max_attempts=2 policy on the 3rd.
    $r1 = $middleware->process(rateLimitMiddlewareTestLoginRequest(), $context, $next);
    $r2 = $middleware->process(rateLimitMiddlewareTestLoginRequest(), $context, $next);
    $r3 = $middleware->process(rateLimitMiddlewareTestLoginRequest(), $context, $next);

    // The critical assertion: even the request that exceeded the limit
    // still reaches $next() and gets a normal response. Never blocked.
    expect($r1)->toBeInstanceOf(Response::class);
    expect($r2)->toBeInstanceOf(Response::class);
    expect($r3)->toBeInstanceOf(Response::class);

    $reportFile = $basePath . '/var/reports/rate-limit-events.jsonl';
    expect(is_file($reportFile))->toBeTrue();

    $lines = array_values(array_filter(explode("\n", (string) file_get_contents($reportFile))));
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
        'identity_salt' => 'test-salt',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 1, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $context = new RouteContext('GET', '/admin/pages');
    $request = new Request(method: 'GET', path: '/admin/pages', clientIp: '203.0.113.10');

    $nextCalled = false;
    $middleware->process($request, $context, function () use (&$nextCalled): Response {
        $nextCalled = true;
        return Response::html('ok');
    });

    expect($nextCalled)->toBeTrue();
    expect(is_file($basePath . '/var/reports/rate-limit-events.jsonl'))->toBeFalse();
});

it('does not touch the request when enabled but mode is enforce (not yet built, per the ADR)', function (): void {
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => true,
        'mode' => 'enforce',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => 'test-salt',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 1, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $request = rateLimitMiddlewareTestLoginRequest();
    $context = new RouteContext('POST', '/admin/login');

    $nextCalled = false;
    $middleware->process($request, $context, function () use (&$nextCalled): Response {
        $nextCalled = true;
        return Response::html('ok');
    });

    expect($nextCalled)->toBeTrue();
});

// --- New tests below: identity-salt enforcement (2026-07-30) ---

it('SECURITY: throws when rate limiting is enabled with an empty identity salt', function (): void {
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => true,
        'mode' => 'report_only',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => '', // the insecure default
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 5, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $request = rateLimitMiddlewareTestLoginRequest();
    $context = new RouteContext('POST', '/admin/login');

    expect(fn () => $middleware->process($request, $context, static fn (): Response => Response::html('ok')))
        ->toThrow(RuntimeException::class, 'Rate limiting is enabled but no identity salt is configured');
});

it('SECURITY: does NOT throw for an empty identity salt while rate limiting remains disabled (the real default)', function (): void {
    // Confirms the enforcement genuinely only triggers once rate limiting
    // is explicitly turned on — never affects a default installation, even
    // though the default config also ships identity_salt as empty.
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => false,
        'mode' => 'report_only',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => '',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 5, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $request = rateLimitMiddlewareTestLoginRequest();
    $context = new RouteContext('POST', '/admin/login');

    $nextCalled = false;
    $middleware->process($request, $context, function () use (&$nextCalled): Response {
        $nextCalled = true;
        return Response::html('ok');
    });

    expect($nextCalled)->toBeTrue();
});

it('SECURITY: works correctly and produces a genuinely salted hash when a real identity salt is configured', function (): void {
    $basePath = rateLimitMiddlewareTestBasePath([
        'enabled' => true,
        'mode' => 'report_only',
        'report_path' => 'var/reports/rate-limit-events.jsonl',
        'identity_salt' => 'a-real-random-salt-value',
        'policies' => ['admin.login' => ['scope' => 'admin', 'max_attempts' => 5, 'window_seconds' => 300]],
    ]);

    $middleware = new RateLimitReportOnlyAdminMiddleware(rateLimitMiddlewareTestPdo(), $basePath);
    $request = rateLimitMiddlewareTestLoginRequest();
    $context = new RouteContext('POST', '/admin/login');

    $nextCalled = false;
    $middleware->process($request, $context, function () use (&$nextCalled): Response {
        $nextCalled = true;
        return Response::html('ok');
    });

    expect($nextCalled)->toBeTrue();

    $reportFile = $basePath . '/var/reports/rate-limit-events.jsonl';
    $event = json_decode((string) file_get_contents($reportFile), true);

    // Confirm the recorded identity hash is NOT simply an unsalted
    // sha256('admin@example.test|203.0.113.10') — proving the configured
    // salt genuinely participated in the hash, not just that a hash exists.
    $unsaltedHash = hash('sha256', '|admin@example.test|203.0.113.10');
    expect($event['identity_hash'])->not->toBe($unsaltedHash);
    expect($event['identity_hash'])->toMatch('/^[a-f0-9]{64}$/');
});
