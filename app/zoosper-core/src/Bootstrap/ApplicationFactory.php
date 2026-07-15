<?php

declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use PDO;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Container\ServiceProviderLoader;
use Zoosper\Core\Database\ConnectionFactory;
use Zoosper\Core\Http\Application;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Core\Log\LogManager;
use Zoosper\Core\Log\ModuleLoggerProviderLoader;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Routing\ControllerProviderLoader;
use Zoosper\Core\Routing\ModuleRouteLoader;
use Zoosper\Core\Routing\Router;
use Zoosper\Core\Security\SecurityHeaders;
use Zoosper\Page\Controller\PageController;

final class ApplicationFactory
{
    /**
     * Build the HTTP application and load module-owned service providers.
     *
     * The core bootstrap now registers only true bootstrap primitives: config,
     * PDO, module registry, logging/error handling and router construction.
     * Feature services must be declared by modules in config/services.php so
     * marketplace/local modules can add or override behaviour without editing
     * this file.
     */
    public static function create(string $basePath): Application
    {
        $config = ConfigRepository::fromPath($basePath . '/config');
        $pdo = (new ConnectionFactory($config, $basePath))->create();
        $modules = new ModuleRegistry($basePath);

        $logManager = new LogManager($config, $basePath);
        $errorHandler = new ErrorHandler($logManager->exceptions());
        $errorHandler->register();

        $services = new ServiceContainer();
        $services->set(ConfigRepository::class, $config);
        $services->set(ModuleRegistry::class, $modules);
        $services->set(PDO::class, $pdo);
        $services->set(LogManager::class, $logManager);
        $services->set(ErrorHandler::class, $errorHandler);
        $services->set('logger.default', $logManager->default());
        $services->set('logger.exception', $logManager->exceptions());

        (new ModuleLoggerProviderLoader($modules, $logManager, $services))->register();
        (new ServiceProviderLoader($modules, $services))->register();
        // Phase 1.00: load root service providers before controller providers are created.
        (new \Zoosper\Core\Bootstrap\ServiceProviderManifestLoader($basePath))->load($services);


        $controllers = (new ControllerProviderLoader($modules, $services))->load();

        // Phase 1.27: inject ErrorHandler so the router logs uncaught exceptions.
        $router = new Router($errorHandler);
        $routeLoader = new ModuleRouteLoader($modules, $controllers);
        $routeLoader->registerAdminRoutes($router);
        $routeLoader->registerApiRoutes($router);

        $pageController = $services->get(PageController::class);

        $router->fallback(static function (Request $request) use ($pageController): Response {
            if (str_starts_with($request->path(), '/api/')) {
                return Response::json([
                    'success' => false,
                    'error' => [
                        'code' => 'route_not_found',
                        'message' => 'API route not found.',
                    ],
                ], 404);
            }

            return $pageController->view($request);
        });
        // Phase 0.99.1: load root service providers declared in config/service_providers.php.
        (new \Zoosper\Core\Bootstrap\ServiceProviderManifestLoader($basePath))->load($services);


        return new Application(
            $router,
            new SecurityHeaders($config->array('security.headers')),
        );
    }
}
