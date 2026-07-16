<?php

declare(strict_types=1);

namespace Zoosper\Auth\Http;

use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Fail-secure admin authentication + authorisation guard.
 *
 * Phase 1.33 (fail-secure, decoupled checks):
 *   - public route            -> always allowed through.
 *   - non-public route        -> a valid authenticated session is REQUIRED,
 *                                even when no specific permission is declared.
 *   - non-public + permission -> additionally requires that ACL permission.
 *
 * On failure it redirects to the admin login route, matching existing controller
 * behaviour. Applied to admin routes only; the stateless API pipeline is not
 * affected.
 */
final readonly class AuthenticationMiddleware implements RouteMiddleware
{
    public function __construct(
        private SessionGuard $guard,
        private string $loginPath = '/admin/login',
    ) {
    }

    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        if ($context->isPublic) {
            return $next($request);
        }

        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect($this->loginPath);
        }

        if ($context->permission !== null && !$user->can($context->permission)) {
            return Response::redirect($this->loginPath);
        }

        return $next($request);
    }
}