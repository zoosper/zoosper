<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

use Throwable;
use Zoosper\Core\Routing\Router;
use Zoosper\Core\Security\SecurityHeaders;
use Zoosper\Core\Site\SiteContextResolver;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Error\ErrorHandler;

final readonly class Application
{
    public function __construct(
        private Router $router,
        private SecurityHeaders $securityHeaders,
        private ServiceContainer $services,
        private ?SiteContextResolver $siteResolver = null,
        private ?ErrorHandler $errorHandler = null,
        private ?\SessionHandlerInterface $sessionHandler = null,
    ) {
    }

    public function services(): ServiceContainer
    {
        return $this->services;
    }

    public function handle(): void
    {
        $request = Request::fromGlobals();
        ProductionSecurityPolicy::assertEnvironment();
        if (!$this->router->isStateless($request) && session_status() !== PHP_SESSION_ACTIVE) {
            session_name((string) env('SESSION_NAME', 'ZOOSPERSESSID'));
            $sessionLifetime = max(300, min(604800, (int) env('SESSION_LIFETIME_SECONDS', 28800)));
            ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_httponly', '1');
            if ($this->sessionHandler !== null) {
                session_set_save_handler($this->sessionHandler, true);
            }

            $sameSite = self::normaliseSameSite((string) env('SESSION_SAMESITE', 'Lax'));
            $secure = filter_var(env('SESSION_SECURE', self::requestIsHttps()), FILTER_VALIDATE_BOOLEAN);
            if ($sameSite === 'None' && !$secure) {
                $sameSite = 'Lax';
            }

            session_set_cookie_params([
                'lifetime' => $sessionLifetime,
                'secure' => $secure,
                'httponly' => true,
                'samesite' => $sameSite,
                'path' => '/',
            ]);
            session_start();
        }

        $this->securityHeaders->apply();

        if ($this->siteResolver !== null) {
            $request = $request->withSiteContext(
                $this->siteResolver->resolve($request->host(), $request->path()),
            );
        }

        try {
            $response = $this->router->dispatch($request);
        } catch (Throwable $exception) {
            $this->errorHandler?->logException($exception, [
                'path' => $request->path(),
                'method' => $request->method(),
                'boundary' => 'application',
            ]);
            $response = $this->errorHandler?->httpResponse(
                $exception,
                str_starts_with($request->path(), '/api/'),
            ) ?? Response::json([
                'success' => false,
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'Zoosper encountered an unexpected error.',
                ],
            ], 500);
        }

        $response->send();
    }

    public static function isStatelessPublicPath(string $path): bool
    {
        return in_array($path, ['/sitemap.xml', '/robots.txt', '/api/v1/health', '/api/v1/hello', '/api/v1/content/page', '/api/v1/menu'], true);
    }

    public static function normaliseSameSite(string $value): string
    {
        return match (strtolower(trim($value))) {
            'strict' => 'Strict',
            'none' => 'None',
            default => 'Lax',
        };
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
        return TrustedProxyResolver::fromEnvironment()->isHttps($_SERVER);
    }
}










