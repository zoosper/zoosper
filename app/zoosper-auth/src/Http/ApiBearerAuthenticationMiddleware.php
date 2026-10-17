<?php

declare(strict_types=1);

namespace Zoosper\Auth\Http;

use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Middleware\ApiAuthenticationMiddleware;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/** @psalm-api Discovered through config/api_middleware.php and the service manifest. */
final readonly class ApiBearerAuthenticationMiddleware implements ApiAuthenticationMiddleware
{
    public function __construct(private PersonalAccessTokenAuthenticator $auth, private JsonResponder $json) {}

    #[\Override]
    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        if ($context->apiAuth !== 'bearer') {
            return $next($request);
        }
        $principal = $this->auth->authenticate($request->header('authorization'));
        if ($principal === null) {
            return $this->json->error('invalid_bearer_token', 'A valid bearer token is required.', 401);
        }
        if ($context->apiScope !== null && !$principal->allows($context->apiScope)) {
            return $this->json->error('insufficient_scope', 'The bearer token cannot perform this operation.', 403);
        }
        if ($context->permissions !== [] && !array_any($context->permissions, $principal->user->can(...))) {
            return $this->json->error('insufficient_permission', 'The authenticated user cannot perform this operation.', 403);
        }
        return $next($request);
    }
}
