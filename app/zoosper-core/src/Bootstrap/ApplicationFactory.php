<?php

declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use PDO;
use Zoosper\Core\Asset\AssetController;
use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\AssetResolver;
use Zoosper\Core\Asset\AssetRouteRegistrar;
use Zoosper\Core\Asset\ModuleAssetManifestLoader;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Config\ModuleConfigAggregator;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Container\ServiceProviderLoader;
use Zoosper\Core\Database\ConnectionFactory;
use Zoosper\Core\Http\Application;
use Zoosper\Core\Http\Middleware\ModuleAdminMiddlewareLoader;
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
use Zoosper\Core\Routing\FallbackHandlerInterface;
use Zoosper\Core\Routing\NullFallbackHandler;

final class ApplicationFactory
{
    /**
     * Build the HTTP application and load module-owned service providers.
     *
     * BOOT-ORDER FIX (confirmed 2026-07-30, external reviewer pass): the
     * database connection (ConnectionFactory::create()) previously
     * happened BEFORE ErrorHandler::register(). A database failure during
     * boot — arguably the single most common real production incident
     * (wrong credentials, DB server down, network partition) — was
     * therefore handled by whatever PHP's own display_errors setting
     * happened to be, rather than by this application's own error
     * handler, risking a raw stack trace (potentially including
     * connection details) being shown or logged in an uncontrolled way.
     *
     * Fixed by moving the PDO connection to AFTER $errorHandler->register()
     * — a pure reorder, no other logic changed. ErrorHandler only needs
     * $config and $basePath, neither of which depend on a working
     * database connection, so this reorder carries no functional risk.
     */
    public static function create(string $basePath): Application
    {
        $modules = new ModuleRegistry($basePath);
        $config = ConfigRepository::fromArray(
            (new ModuleConfigAggregator($modules, $basePath . '/config'))->aggregate()
        );

        $logManager = new LogManager($config, $basePath);
        $errorHandler = new ErrorHandler($logManager->exceptions());
        $errorHandler->register();

        // BOOT-ORDER FIX: this now runs AFTER error-handler registration
        // (previously ran before it) — see class docblock above.
        $pdo = (new ConnectionFactory($config, $basePath))->create();

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
        // Load root service providers before controller providers are created.
        (new ServiceProviderManifestLoader($basePath))->load($services);

        $controllers = (new ControllerProviderLoader($modules, $services))->load();

        // Phase 1.27: inject ErrorHandler so the router logs uncaught exceptions.
        $router = new Router($errorHandler);
        $routeLoader = new ModuleRouteLoader($modules, $controllers);

        // Phase 1.33: admin routes run through the module-contributed middleware
        // pipeline (auth guard + CSRF). API routes stay unwrapped/stateless.
        $adminMiddleware = (new ModuleAdminMiddlewareLoader($modules, $services))->load();
        $routeLoader->registerAdminRoutes($router, $adminMiddleware);
        $routeLoader->registerApiRoutes($router);

        // Phase C1/C2: register the module asset pipeline route
        // (GET+HEAD /asset/{module}/{path}) directly on the router —
        // deliberately NOT through registerAdminRoutes(), so it carries no
        // auth/CSRF middleware, consistent with module CSS/JS having always
        // been served as unauthenticated static files. AssetResolver's
        // path-traversal and extension-allowlist checks are the real security
        // boundary here. Cache TTL/immutability are configurable via
        // config/asset_pipeline.php (Phase C2) rather than hard-coded.
        $assetPipelineConfig = $config->array('asset_pipeline');
        $assetModules = new AssetModuleRegistry();
        (new ModuleAssetManifestLoader($modules))->registerInto($assetModules);
        $assetController = new AssetController(
            new AssetResolver($assetModules),
            (int) ($assetPipelineConfig['cache_max_age'] ?? 31536000),
            (bool) ($assetPipelineConfig['cache_immutable'] ?? true),
        );
        AssetRouteRegistrar::register($router, $assetController);

        $fallbackHandler = $services->has(FallbackHandlerInterface::class)
            ? $services->get(FallbackHandlerInterface::class)
            : new NullFallbackHandler();

        $router->fallback(static function (Request $request) use ($fallbackHandler): Response {
            if (str_starts_with($request->path(), '/api/')) {
                return Response::json([
                    'success' => false,
                    'error' => [
                        'code' => 'route_not_found',
                        'message' => 'API route not found.',
                    ],
                ], 404);
            }

            // The previous code called ->view(), which does not exist on the
            // contract or on NullFallbackHandler, fataling every frontend request.
            // Phase 1.93: use the FallbackHandlerInterface contract (supports/handle).
            if ($fallbackHandler->supports($request)) {
                $response = $fallbackHandler->handle($request);
                if ($response instanceof Response) {
                    return $response;
                }
            }

            return Response::html('Page not found.', 404);
        });

        return new Application(
            $router,
            new SecurityHeaders($config->array('security.headers'), $config->array('security.csp'), $config->array('security.hsts')),
            $services->get(\Zoosper\Core\Site\SiteContextResolver::class),
        );
    }
}
