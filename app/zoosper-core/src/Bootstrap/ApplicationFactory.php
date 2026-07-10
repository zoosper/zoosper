<?php

declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Admin\UI\AdminComponentRenderer;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\App\CmsVersion;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Database\ConnectionFactory;
use Zoosper\Core\Http\Application;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Core\Log\LogManager;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Routing\ControllerProviderLoader;
use Zoosper\Core\Routing\ModuleRouteLoader;
use Zoosper\Core\Routing\Router;
use Zoosper\Core\Security\SecurityHeaders;
use Zoosper\Page\Controller\PageController;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Site\Service\SiteResolver;
use Zoosper\Theme\Layout\LayoutUpdateRepository;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\ThemeRepository;
use Zoosper\Theme\Theme\ThemeResolver;

final class ApplicationFactory
{
    public static function create(string $basePath): Application
    {
        $config = ConfigRepository::fromPath($basePath . '/config');

        $logManager = new LogManager($config, $basePath);
        $errorHandler = new ErrorHandler($logManager->exceptions());
        $errorHandler->register();

        $pdo = (new ConnectionFactory($config, $basePath))->create();
        $modules = new ModuleRegistry($basePath);

        /*
         * Core repositories.
         *
         * These are still central infrastructure services. Feature-specific
         * controller creation now lives in each module's config/controllers.php.
         */
        $userRepository = new AdminUserRepository($pdo);
        $roleRepository = new RoleRepository($pdo);
        $siteRepository = new SiteRepository($pdo);
        $pageRepository = new PageRepository($pdo);
        $loginHistoryRepository = new LoginHistoryRepository($pdo);
        $auditLogRepository = new AuditLogRepository($pdo);
        $themeRepository = new ThemeRepository($basePath . '/themes');

        /*
         * Core services.
         */
        $passwordHasher = new PasswordHasher();
        $auth = new AuthService($userRepository, $passwordHasher);
        $guard = new SessionGuard($userRepository);
        $csrf = new CsrfTokenManager();
        $json = new JsonResponder();
        $cmsVersion = new CmsVersion($config);
        $auditLogger = new AuditLogger($auditLogRepository);

        /*
         * Theme/layout services.
         *
         * Important:
         * LayoutUpdateRepository must be created before TemplateRenderer so
         * frontend and admin renderers both support remove/replace/inject
         * layout updates.
         */
        $layoutUpdates = new LayoutUpdateRepository();

        $frontendTemplateRenderer = new TemplateRenderer(
            new ThemeResolver($basePath . '/themes', 'default'),
            $modules,
            $layoutUpdates,
        );

        $adminTemplateRenderer = new TemplateRenderer(
            new ThemeResolver($basePath . '/themes/admin', 'default'),
            $modules,
            $layoutUpdates,
        );

        /*
         * Admin shell services.
         */
        $adminMenu = new AdminMenu(new AdminMenuLoader($modules));
        $adminLayout = new AdminLayout($adminMenu, $config, $adminTemplateRenderer);
        $adminViewRenderer = new AdminViewRenderer($adminTemplateRenderer, $adminLayout);
        $adminComponentRenderer = new AdminComponentRenderer($adminTemplateRenderer);

        /*
         * Site/page rendering services.
         */
        $siteResolver = new SiteResolver($siteRepository);
        $pageRenderer = new PageRenderer($frontendTemplateRenderer, $cmsVersion, $modules);
        /**
         * Frontend fallback controller.
         *
         * This remains in ApplicationFactory because it is the final non-admin,
         * non-API fallback route for public page rendering.
         */
        $pageController = new PageController($siteResolver, $pageRepository, $pageRenderer);
        /**
         * Shared service container for module-owned controller providers.
         *
         * Modules should create controllers in:
         *
         * app/<module>/config/controllers.php
         *
         * ApplicationFactory should only register shared infrastructure here.
         */
        $services = new ServiceContainer();

        $services->set(ConfigRepository::class, $config);
        $services->set(ModuleRegistry::class, $modules);
        $services->set(LogManager::class, $logManager);
        $services->set(ErrorHandler::class, $errorHandler);

        $services->set(AdminUserRepository::class, $userRepository);
        $services->set(RoleRepository::class, $roleRepository);
        $services->set(SiteRepository::class, $siteRepository);
        $services->set(PageRepository::class, $pageRepository);
        $services->set(LoginHistoryRepository::class, $loginHistoryRepository);
        $services->set(AuditLogRepository::class, $auditLogRepository);
        $services->set(ThemeRepository::class, $themeRepository);

        $services->set(PasswordHasher::class, $passwordHasher);
        $services->set(AuthService::class, $auth);
        $services->set(SessionGuard::class, $guard);
        $services->set(CsrfTokenManager::class, $csrf);
        $services->set(JsonResponder::class, $json);
        $services->set(CmsVersion::class, $cmsVersion);
        $services->set(AuditLogger::class, $auditLogger);

        $services->set(AdminMenu::class, $adminMenu);
        $services->set(AdminLayout::class, $adminLayout);
        $services->set(AdminViewRenderer::class, $adminViewRenderer);
        $services->set(AdminComponentRenderer::class, $adminComponentRenderer);

        $services->set(SiteResolver::class, $siteResolver);
        $services->set(TemplateRenderer::class, $frontendTemplateRenderer);
        $services->set(PageRenderer::class, $pageRenderer);
        $services->set(PageController::class, $pageController);

        $services->set('logger.default', $logManager->default());
        $services->set('logger.exception', $logManager->exceptions());
        $services->set('logger.zoosper-admin', $logManager->module('zoosper-admin'));
        $services->set('logger.zoosper-api', $logManager->module('zoosper-api'));
        $services->set('logger.zoosper-auth', $logManager->module('zoosper-auth'));
        $services->set('logger.zoosper-core', $logManager->module('zoosper-core'));
        $services->set('logger.zoosper-page', $logManager->module('zoosper-page'));
        $services->set('logger.zoosper-site', $logManager->module('zoosper-site'));
        $services->set('logger.zoosper-theme', $logManager->module('zoosper-theme'));

        /**
         * Load controllers from module-owned provider files.
         *
         * This keeps ApplicationFactory from growing every time a module adds
         * a controller.
         */
        $controllers = (new ControllerProviderLoader($modules, $services))->load();

        $router = new Router();
        $routeLoader = new ModuleRouteLoader($modules, $controllers);
        $routeLoader->registerAdminRoutes($router);
        $routeLoader->registerApiRoutes($router);

        /*
         * Public frontend fallback.
         */
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

        return new Application($router, new SecurityHeaders($config->array('security.headers')));
    }
}
