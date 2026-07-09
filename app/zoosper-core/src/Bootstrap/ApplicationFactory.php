<?php

declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Api\Controller\HealthController;
use Zoosper\Api\Controller\HelloController as ApiHelloController;
use Zoosper\Api\Controller\MeController;
use Zoosper\Auth\Access\InMemoryRoleProvider;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Http\Application;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\Router;
use Zoosper\Core\Security\SecurityHeaders;
use Zoosper\Page\Controller\HomeController;

final class ApplicationFactory
{
    public static function create(string $basePath): Application
    {
        $config = ConfigRepository::fromPath($basePath . '/config');
        $router = new Router();
        $roles = InMemoryRoleProvider::createDefault();

        $router->get('/', [new HomeController(), 'index']);
        $router->get('/admin', [new DashboardController($roles), 'index']);
        $router->get('/api/v1/health', [new HealthController(new JsonResponder()), 'show']);
        $router->get('/api/v1/hello', [new ApiHelloController(new JsonResponder()), 'show']);
        $router->get('/api/v1/me', [new MeController(new JsonResponder(), $roles), 'show']);

        $router->fallback(static function (Request $request): Response {
            if (str_starts_with($request->path(), '/api/')) {
                return Response::json([
                    'success' => false,
                    'error' => [
                        'code' => 'route_not_found',
                        'message' => 'API route not found.',
                    ],
                ], 404);
            }

            return Response::html('<h1>404</h1><p>Zoosper route not found.</p>', 404);
        });

        return new Application(
            router: $router,
            securityHeaders: new SecurityHeaders($config->array('security.headers')),
        );
    }
}
