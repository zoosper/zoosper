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
 *   - public route            -> always allowed through.
 *   - non-public route        -> a valid authenticated session is REQUIRED.
 *   - non-public + permission -> allowed if the user has ANY ONE of the
 *                                declared permissions (OR semantics, 1.33b).
 *
 * Applied to admin routes only; the stateless API pipeline is not affected.
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

        $required = $context->requiresAnyPermission();
        if ($required === []) {
            return $next($request);
        }

        foreach ($required as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        return Response::redirect($this->loginPath);
    }
}