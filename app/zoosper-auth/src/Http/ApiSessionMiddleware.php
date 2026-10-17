<?php

declare(strict_types=1);

namespace Zoosper\Auth\Http;

use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Middleware\ApiSessionAuthenticationMiddleware;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/** @psalm-api Discovered through config/api_middleware.php and the service manifest. */
final readonly class ApiSessionMiddleware implements ApiSessionAuthenticationMiddleware
{
    public function __construct(private SessionGuard $guard, private JsonResponder $json) {}

    #[\Override]
    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        if ($context->apiAuth !== 'session') {
            return $next($request);
        }
        if ($this->guard->user() === null) {
            return $this->json->error('unauthenticated', 'You are not logged in.', 401);
        }
        return $next($request);
    }
}
