<?php

declare(strict_types=1);

namespace Zoosper\Auth\Http;

use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * HTML Admin-login transport adapter for the canonical authentication limiter.
 *
 * The historical class name remains stable for module middleware registration.
 * Policy resolution, identity hashing, persistence, report-only diagnostics and
 * enforcement decisions belong exclusively to AdminAuthenticationRateLimiter.
 */
final readonly class RateLimitReportOnlyAdminMiddleware implements RouteMiddleware
{
    public function __construct(
        private AdminAuthenticationRateLimiterInterface $rateLimiter,
        private string $loginPath = '/admin/login',
    ) {
    }

    public function process(
        Request $request,
        RouteContext $context,
        callable $next,
    ): Response {
        if (
            $request->path() !== $this->loginPath
            || $request->method() !== 'POST'
        ) {
            return $next($request);
        }

        $email = trim(
            (string) ($request->form()['email'] ?? '')
        );
        $clientIp = $request->clientIp();

        if (
            $email === ''
            && ($clientIp === null || trim($clientIp) === '')
        ) {
            return $next($request);
        }

        $decision = $this->rateLimiter->checkPasswordLogin(
            $email,
            $clientIp,
        );

        if ($decision->allowed) {
            return $next($request);
        }

        return Response::raw(
            '<!doctype html><html lang="en">'
            . '<head><meta charset="utf-8">'
            . '<title>Too many requests</title></head>'
            . '<body><main>'
            . '<h1>Too many sign-in attempts</h1>'
            . '<p>Please wait before trying again.</p>'
            . '</main></body></html>',
            429,
            [
                'Content-Type' => 'text/html; charset=utf-8',
                'Retry-After' => (string) max(
                    1,
                    $decision->retryAfterSeconds,
                ),
                'Cache-Control' => 'no-store',
            ],
        );
    }
}