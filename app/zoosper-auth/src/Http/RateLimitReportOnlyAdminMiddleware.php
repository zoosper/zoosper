<?php

declare(strict_types=1);

namespace Zoosper\Auth\Http;

use PDO;
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
 * Wires the existing report-only rate-limit stack into the real admin
 * RouteMiddleware pipeline. This middleware never blocks a request. It only
 * records diagnostics when rate limiting is explicitly enabled in report_only
 * mode. HTTP 429 enforcement remains a deliberately deferred future phase.
 */
final class RateLimitReportOnlyAdminMiddleware implements RouteMiddleware
{
    private const POLICY_KEY = 'admin.login';
    private const LOGIN_PATH = '/admin/login';

    public function __construct(private readonly PDO $pdo, private readonly string $basePath)
    {
    }

    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        if ($request->path() !== self::LOGIN_PATH || $request->method() !== 'POST') {
            return $next($request);
        }

        $config = $this->loadRuntimeConfig();
        if (!$config->enabled || !$config->isReportOnly()) {
            return $next($request);
        }

        $email = trim((string) ($request->form()['email'] ?? ''));
        $ip = $request->clientIp() ?? '';
        $identityParts = array_values(array_filter([$email, $ip], static fn (string $part): bool => $part !== ''));

        if ($identityParts === []) {
            return $next($request);
        }

        $rateLimitContext = (new AdminRateLimitContextFactory(new RateLimitIdentityHasher(), $config))
            ->create(self::POLICY_KEY, $identityParts);

        $store = new DatabaseRateLimitStore($this->pdo);
        $store->ensureSchema();

        $middleware = new ReportOnlyRateLimitMiddleware(
            new RateLimitGuard(
                new StaticRateLimitPolicyResolver($config->policies),
                new RateLimitEnforcer($store),
            ),
            new FileRateLimitReportSink($this->basePath . '/' . ltrim($config->reportPath, '/')),
        );

        return $middleware->handle($rateLimitContext, static fn (): Response => $next($request));
    }

    private function loadRuntimeConfig(): RateLimitRuntimeConfig
    {
        $configPath = $this->basePath . '/app/zoosper-core/config/rate_limit.php';
        $config = is_file($configPath) ? require $configPath : [];

        return RateLimitRuntimeConfig::fromArray(is_array($config) ? $config : []);
    }
}
