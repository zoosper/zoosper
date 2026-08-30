<?php

declare(strict_types=1);

namespace Zoosper\Auth\Http;

use PDO;
use RuntimeException;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Security\RateLimit\AdminRateLimitContextFactory;
use Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore;
use Zoosper\Core\Security\RateLimit\FileRateLimitReportSink;
use Zoosper\Core\Security\RateLimit\RateLimitEnforcer;
use Zoosper\Core\Security\RateLimit\RateLimitGuard;
use Zoosper\Core\Security\RateLimit\RateLimitIdentityHasher;
use Zoosper\Core\Security\RateLimit\RateLimitRuntimeConfig;
use Zoosper\Core\Security\RateLimit\ReportOnlyRateLimitMiddleware;
use Zoosper\Core\Security\RateLimit\StaticRateLimitPolicyResolver;

/**
 * Wires the admin login rate-limit stack into the RouteMiddleware pipeline.
 * Supports both report-only diagnostics and active HTTP 429 enforcement based
 * on the configured rate-limit mode (RATE_LIMIT_MODE=report_only|enforce).
 *
 * SECURITY FIX (confirmed 2026-07-30, external reviewer pass):
 * app/zoosper-core/config/rate_limit.php ships with 'identity_salt' => ''
 * (an empty string) by default. RateLimitIdentityHasher hashes the caller's
 * email+IP with SHA-256 using this salt — with no salt at all, that hash is
 * a straightforward dictionary/rainbow-table target for common email+IP
 * pairs, NOT the "opaque hash" the surrounding docblocks describe.
 *
 * LESSON APPLIED FROM THE 2FA ENCRYPTION KEY FIX (same session, several
 * rounds needed to get right): this enforcement is DELIBERATELY placed
 * here, in the middleware's process() method, guarded behind the EXISTING
 * `$config->enabled && $config->isReportOnly()` check — NOT inside
 * rate_limit.php itself. That config file is still eagerly `require`d
 * alongside every other config file by ConfigRepository::fromPath()
 * (whenever ANY config is requested), so making IT throw would risk the
 * exact same "unrelated boot/test failures" problem the 2FA config fix hit.
 * Because rate limiting ships DISABLED by default, and this check only
 * runs once already past that "is it actually enabled" gate, this can
 * NEVER trigger for any environment that hasn't explicitly turned rate
 * limiting on — safe by construction, not by luck.
 *
 * A random salt is deliberately NOT auto-generated at runtime: the same
 * identity must hash to the SAME value across every request to correctly
 * accumulate a rate-limit bucket count. A fresh random salt on every
 * request/process would silently break rate-limiting correctness itself
 * (every request would look like a brand-new identity). A real salt must
 * come from a persisted, operator-provided secret — hence failing loudly
 * and telling the operator how to generate one, rather than silently
 * inventing an unstable one.
 */
final class RateLimitReportOnlyAdminMiddleware implements RouteMiddleware
{
    private const POLICY_KEY = 'admin.login';
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $basePath,
        private readonly string $loginPath = '/admin/login',
    ) {
    }

    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        if ($request->path() !== $this->loginPath || $request->method() !== 'POST') {
            return $next($request);
        }

        $config = $this->loadRuntimeConfig();
        if (!$config->enabled) {
            return $next($request);
        }

        // SECURITY FIX: only reached once rate limiting is explicitly
        // enabled — never affects a default (disabled) installation.
        if (trim($config->identitySalt) === '') {
            throw new RuntimeException(
                'Rate limiting is enabled but no identity salt is configured. Set the '
                . 'RATE_LIMIT_IDENTITY_SALT environment variable to a strong, random secret '
                . 'before enabling rate limiting. Without a real salt, the identity hash used '
                . 'to track login attempts is vulnerable to dictionary/rainbow-table attacks '
                . 'against common email+IP combinations, rather than being genuinely opaque. '
                . 'Generate one with, for example: php -r "echo bin2hex(random_bytes(32));" '
                . 'and set it as RATE_LIMIT_IDENTITY_SALT in your .env file.'
            );
        }

        $email = strtolower(trim((string) ($request->form()['email'] ?? '')));
        $ip = $request->clientIp() ?? '';
        $identityParts = array_values(array_filter([$email, $ip], static fn (string $part): bool => $part !== ''));

        if ($identityParts === []) {
            return $next($request);
        }

        $rateLimitContext = (new AdminRateLimitContextFactory(new RateLimitIdentityHasher(), $config))
            ->create(self::POLICY_KEY, $identityParts);

        $store = new DatabaseRateLimitStore($this->pdo);
        $store->ensureSchema();

        $guard = new RateLimitGuard(
            new StaticRateLimitPolicyResolver($config->policies),
            new RateLimitEnforcer($store),
        );

        if ($config->isReportOnly()) {
            return (new ReportOnlyRateLimitMiddleware(
                $guard,
                new FileRateLimitReportSink($this->basePath . '/' . ltrim($config->reportPath, '/')),
            ))->handle($rateLimitContext, static fn (): Response => $next($request));
        }

        $decision = $guard->check($rateLimitContext);
        if ($decision->allowed) {
            return $next($request);
        }

        $retryAfter = max(1, $decision->retryAfterSeconds);
        return Response::raw(
            '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Too many requests</title></head>'
            . '<body><main><h1>Too many sign-in attempts</h1><p>Please wait before trying again.</p></main></body></html>',
            429,
            [
                'Content-Type' => 'text/html; charset=utf-8',
                'Retry-After' => (string) $retryAfter,
                'Cache-Control' => 'no-store',
            ],
        );
    }

    private function loadRuntimeConfig(): RateLimitRuntimeConfig
    {
        $configPath = $this->basePath . '/app/zoosper-core/config/rate_limit.php';
        $config = is_file($configPath) ? require $configPath : [];

        return RateLimitRuntimeConfig::fromArray(is_array($config) ? $config : []);
    }
}
