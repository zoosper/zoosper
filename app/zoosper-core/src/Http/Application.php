<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

use Throwable;
use Zoosper\Core\Routing\Router;
use Zoosper\Core\Security\SecurityHeaders;
use Zoosper\Core\Site\SiteContextResolver;

final readonly class Application
{
    public function __construct(
        private Router $router,
        private SecurityHeaders $securityHeaders,
        private ?SiteContextResolver $siteResolver = null,
    ) {
    }

    public function handle(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name((string) env('SESSION_NAME', 'ZOOSPERSESSID'));
            $sessionLifetime = max(300, min(604800, (int) env('SESSION_LIFETIME_SECONDS', 28800)));
            ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
            session_set_cookie_params([
                'lifetime' => $sessionLifetime,
                // Phase 1.100: the secure flag now DEFAULTS to whether the current
                // request is served over HTTPS, instead of a hard-coded false.
                //   - Local HTTP dev: default false (cookies still work).
                //   - Production HTTPS: default true automatically, closing the
                //     footgun where forgetting SESSION_SECURE=true leaked the
                //     session cookie over plain HTTP.
                // An explicit SESSION_SECURE env value always wins.
                'secure' => filter_var(env('SESSION_SECURE', self::requestIsHttps()), FILTER_VALIDATE_BOOLEAN),
                'httponly' => true,
                'samesite' => (string) env('SESSION_SAMESITE', 'Lax'),
                'path' => '/',
            ]);
            session_start();
        }

        $this->securityHeaders->apply();
        $request = Request::fromGlobals();

        // Phase 1.34a: resolve the site context ONCE per request, from the request
        // (not $_SERVER), and carry it immutably on the request down the stack. When
        // the resolver is not wired the request simply carries no context (safe).
        if ($this->siteResolver !== null) {
            $request = $request->withSiteContext(
                $this->siteResolver->resolve($request->host(), $request->path()),
            );
        }

        try {
            $response = $this->router->dispatch($request);
        } catch (Throwable $exception) {
            $response = Response::json([
                'success' => false,
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'Zoosper encountered an unexpected error.',
                ],
            ], 500);
        }

        $response->send();
    }

    /**
     * Determine whether the current request is being served over HTTPS.
     *
     * Checks the standard HTTPS server var, the common reverse-proxy header
     * X-Forwarded-Proto, and the canonical HTTPS port. Kept static and
     * dependency-free so it is trivially unit-testable via $_SERVER.
     */
    public static function requestIsHttps(): bool
    {
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
        if ($https !== '' && $https !== 'off') {
            return true;
        }

        $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        if ($forwardedProto === 'https') {
            return true;
        }

        return (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}
