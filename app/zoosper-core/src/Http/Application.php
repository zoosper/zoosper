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
            session_set_cookie_params([
                'secure' => filter_var(env('SESSION_SECURE', false), FILTER_VALIDATE_BOOLEAN),
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
}
