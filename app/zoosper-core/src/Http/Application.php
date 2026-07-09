<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

use Throwable;
use Zoosper\Core\Routing\Router;
use Zoosper\Core\Security\SecurityHeaders;

final readonly class Application
{
    public function __construct(
        private Router $router,
        private SecurityHeaders $securityHeaders,
    ) {
    }

    public function handle(): void
    {
        $this->securityHeaders->apply();
        $request = Request::fromGlobals();

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
