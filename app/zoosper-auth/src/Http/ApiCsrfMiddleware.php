<?php

declare(strict_types=1);

namespace Zoosper\Auth\Http;

use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/** @psalm-api Discovered through config/api_middleware.php and the service manifest. */
final readonly class ApiCsrfMiddleware implements RouteMiddleware
{
    public function __construct(private CsrfTokenManager $csrf, private JsonResponder $json) {}

    #[\Override]
    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        if ($context->apiAuth !== 'session' || $context->apiCsrf !== 'enforce') {
            return $next($request);
        }
        $token = (string) ($request->form()['_csrf_token'] ?? $request->header('x-csrf-token', '') ?? '');
        if (!$this->csrf->isValid($token)) {
            return $this->json->error('csrf_token_invalid', 'The session security token is missing or invalid.', 419);
        }
        return $next($request);
    }
}
