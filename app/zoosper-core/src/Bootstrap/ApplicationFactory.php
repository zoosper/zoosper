<?php

declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use PDO;
use Zoosper\Core\Asset\AssetController;
use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\AssetResolver;
use Zoosper\Core\Asset\AssetRouteRegistrar;
use Zoosper\Core\Asset\ModuleAssetManifestLoader;
use Marko\Config\ConfigRepositoryInterface;
use Zoosper\Core\Config\ApplicationConfigLoader;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Config\Bridge\MarkoConfigRepositoryAdapter;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Container\ServiceProviderLoader;
use Zoosper\Core\Database\ConnectionFactory;
use Zoosper\Core\Http\Application;
use Zoosper\Core\Http\Middleware\ModuleAdminMiddlewareLoader;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Error\ErrorHandler;
use Zoosper\Logger\Manager\LogManager;
use Zoosper\Logger\Module\ModuleLoggerProviderLoader;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Routing\ControllerProviderLoader;
use Zoosper\Core\Routing\ModuleRouteLoader;
use Zoosper\Core\Url\AdminPathCollectionTransformer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Core\Routing\Router;
use Zoosper\Core\Security\SecurityHeaders;
use Zoosper\Core\Routing\FallbackHandlerInterface;
use Zoosper\Core\Routing\NullFallbackHandler;

final class ApplicationFactory
{
    /**
     * Build the HTTP application and load module-owned service providers.
     */
    public static function create(string $basePath): Application
    {
        // Early error handler registration: catch and redact errors during module discovery and config load.
        $earlyLogManager = new LogManager(ConfigRepository::fromArray([]), $basePath);
        $earlyDebug = filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN);
        $earlyErrorHandler = new ErrorHandler(
            $earlyLogManager->exceptions(),
            $earlyDebug,
        );
        $earlyErrorHandler->register();

        $modules = new ModuleRegistry($basePath);
        $config = (new ApplicationConfigLoader($basePath, $modules))->load();
        $markoConfig = new MarkoConfigRepositoryAdapter($config);

        $logManager = new LogManager($config, $basePath);
        $errorHandler = new ErrorHandler(
            $logManager->exceptions(),
            (bool) ($config->get('app.debug', false) ?? false),
        );
        $errorHandler->register();

        $pdo = (new ConnectionFactory($config, $basePath))->create();

        $services = new ServiceContainer();
        $services->set(ConfigRepository::class, $config);
        $services->set(ConfigRepositoryInterface::class, $markoConfig);
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

        $sessionHandler = $services->has(\SessionHandlerInterface::class)
            ? $services->get(\SessionHandlerInterface::class)
            : null;
        $controllers = (new ControllerProviderLoader($modules, $services))->load();

        $router = new Router($errorHandler);
        $routeLoader = new ModuleRouteLoader(
            $modules,
            $controllers,
            $services->get(AdminPathCollectionTransformer::class),
        );

        $adminMiddleware = (new ModuleAdminMiddlewareLoader($modules, $services))->load();
        $routeLoader->registerAdminRoutes($router, $adminMiddleware);
        $routeLoader->registerApiRoutes($router);

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
            $errorHandler,
            $sessionHandler,
        );
    }
}

