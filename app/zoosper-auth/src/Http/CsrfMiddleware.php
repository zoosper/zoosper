<?php

declare(strict_types=1);

namespace Zoosper\Auth\Http;

use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Central CSRF guard for stateful admin requests.
 *
 * Phase 1.33c: validates the _csrf_token form field on state-changing HTTP
 * methods (POST/PUT/PATCH/DELETE). Safe (GET/HEAD/OPTIONS) requests pass
 * through untouched. Applied to the ADMIN pipeline only - the stateless API
 * pipeline is not wrapped, so token-based API auth is unaffected.
 *
 * Controllers keep their own CSRF checks for now (belt-and-braces); those become
 * redundant once this guard is verified and can be removed in a cleanup phase.
 *
 * PCI-aware: never logs or echoes the token value.
 */
final readonly class CsrfMiddleware implements RouteMiddleware
{
    /** @var list<string> */
    private const STATEFUL_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(private CsrfTokenManager $csrf)
    {
    }

    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        if (!in_array($request->method(), self::STATEFUL_METHODS, true)) {
            return $next($request);
        }

        $token = (string) ($request->form()['_csrf_token'] ?? '');
        if (!$this->csrf->isValid($token)) {
            return Response::html('<h1>419</h1><p>Invalid or missing security token. Please reload the page and try again.</p>', 419);
        }

        return $next($request);
    }
}