<?php
declare(strict_types=1);

namespace Zoosper\Core\Http;

use Throwable;
use Zoosper\Core\Routing\Router;
use Zoosper\Core\Security\SecurityHeaders;

final readonly class Application
{
    public function __construct(private Router $router, private SecurityHeaders $securityHeaders)
    {
    }

    public function handle(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name((string)env('SESSION_NAME', 'ZOOSPERSESSID'));
            session_set_cookie_params(['secure' => filter_var(env('SESSION_SECURE', false), FILTER_VALIDATE_BOOLEAN), 'httponly' => true, 'samesite' => (string)env('SESSION_SAMESITE', 'Lax'), 'path' => '/']);
            session_start();
        }
        $this->securityHeaders->apply();
        $request = Request::fromGlobals();
        try {
            $response = $this->router->dispatch($request);
        } catch (Throwable) {
            $response = Response::json(['success' => false, 'error' => ['code' => 'internal_error', 'message' => 'Zoosper encountered an unexpected error.']], 500);
        }
        $response->send();
    }
}
