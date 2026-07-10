<?php

declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use PDO;
use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Form\AdminFormUiConfigLoader;
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
use Zoosper\Core\Log\ModuleLoggerProviderLoader;
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
    /**
     * Build the HTTP application and register shared infrastructure services.
     *
     * ApplicationFactory intentionally avoids creating feature/module controllers
     * directly. Modules should register controllers through their own
     * `config/controllers.php` files so they can be added or removed without
     * editing the core bootstrap.
     */
    public static function create(string $basePath): Application
    {
        $config = ConfigRepository::fromPath($basePath . '/config');

        /*
         * Local logging and error handling are core infrastructure services.
         * Module-specific loggers are discovered from module-owned logging.php
         * files by ModuleLoggerProviderLoader below.
         */
        $logManager = new LogManager($config, $basePath);
        $errorHandler = new ErrorHandler($logManager->exceptions());
        $errorHandler->register();

        $pdo = (new ConnectionFactory($config, $basePath))->create();
        $modules = new ModuleRegistry($basePath);

        /*
         * Core repositories.
         *
         * These are shared infrastructure repositories. Feature-specific
         * controller creation lives in each module's config/controllers.php.
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
        $adminFormUi = new AdminFormUiConfigLoader($modules);

        /*
         * Theme/layout services.
         *
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

        /*
         * Frontend fallback controller.
         *
         * This remains in ApplicationFactory because it is the final non-admin,
         * non-API fallback route for public page rendering.
         */
        $pageController = new PageController($siteResolver, $pageRepository, $pageRenderer);

        /*
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

        /*
         * PDO is registered once as shared infrastructure so module providers
         * can build query services such as PageGridRepository without requiring
         * ApplicationFactory to know about those module-specific services.
         */
        $services->set(PDO::class, $pdo);

        $services->set(LogManager::class, $logManager);
        $services->set(ErrorHandler::class, $errorHandler);
        $services->set(AdminFormUiConfigLoader::class, $adminFormUi);

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

        /*
         * Module loggers are registered from module-owned config/logging.php
         * files. This prevents ApplicationFactory from hard-coding loggers for
         * every installed module.
         */
        (new ModuleLoggerProviderLoader($modules, $logManager, $services))->register();

        /*
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

        return new Application(
            $router,
            new SecurityHeaders($config->array('security.headers')),
        );
    }
}