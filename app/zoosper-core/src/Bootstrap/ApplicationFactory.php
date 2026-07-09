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
        $pdo = (new ConnectionFactory($config, $basePath))->create();
        $modules = new ModuleRegistry($basePath);

        $userRepository = new AdminUserRepository($pdo);
        $roleRepository = new RoleRepository($pdo);
        $siteRepository = new SiteRepository($pdo);
        $pageRepository = new PageRepository($pdo);
        $loginHistoryRepository = new LoginHistoryRepository($pdo);
        $auditLogRepository = new AuditLogRepository($pdo);
        $auditLogger = new AuditLogger($auditLogRepository);
        $themeRepository = new ThemeRepository($basePath . '/themes');

        $passwordHasher = new PasswordHasher();
        $auth = new AuthService($userRepository, $passwordHasher);
        $guard = new SessionGuard($userRepository);
        $csrf = new CsrfTokenManager();
        $json = new JsonResponder();
        $cmsVersion = new CmsVersion($config);

        $adminMenu = new AdminMenu(new AdminMenuLoader($modules));
        $adminTemplateRenderer = new TemplateRenderer(new ThemeResolver($basePath . '/themes/admin', 'default'), $modules);
        $adminLayout = new AdminLayout($adminMenu, $config, $adminTemplateRenderer);

        $siteResolver = new SiteResolver($siteRepository);
        $templateRenderer = new TemplateRenderer(new ThemeResolver($basePath . '/themes', 'default'), $modules);
        $pageRenderer = new PageRenderer($templateRenderer, $cmsVersion, $modules);
        $pageController = new PageController($siteResolver, $pageRepository, $pageRenderer);

        $layoutUpdates = new LayoutUpdateRepository();
        $templateRenderer = new TemplateRenderer(new ThemeResolver($basePath . '/themes', 'default'), $modules, $layoutUpdates);
        $adminTemplateRenderer = new TemplateRenderer(new ThemeResolver($basePath . '/themes/admin', 'default'), $modules, $layoutUpdates);

        $services = new ServiceContainer();
        $services->set(ConfigRepository::class, $config);
        $services->set(ModuleRegistry::class, $modules);
        $services->set(AdminUserRepository::class, $userRepository);
        $services->set(RoleRepository::class, $roleRepository);
        $services->set(SiteRepository::class, $siteRepository);
        $services->set(PageRepository::class, $pageRepository);
        $services->set(LoginHistoryRepository::class, $loginHistoryRepository);
        $services->set(AuditLogRepository::class, $auditLogRepository);
        $services->set(AuditLogger::class, $auditLogger);
        $services->set(ThemeRepository::class, $themeRepository);
        $services->set(PasswordHasher::class, $passwordHasher);
        $services->set(AuthService::class, $auth);
        $services->set(SessionGuard::class, $guard);
        $services->set(CsrfTokenManager::class, $csrf);
        $services->set(JsonResponder::class, $json);
        $services->set(CmsVersion::class, $cmsVersion);
        $services->set(AdminMenu::class, $adminMenu);
        $services->set(AdminLayout::class, $adminLayout);
        $services->set(SiteResolver::class, $siteResolver);
        $services->set(TemplateRenderer::class, $templateRenderer);
        $services->set(PageRenderer::class, $pageRenderer);
        $services->set(PageController::class, $pageController);
        $services->set(AdminViewRenderer::class, new AdminViewRenderer($adminTemplateRenderer, $adminLayout));
        $services->set(AdminComponentRenderer::class, new AdminComponentRenderer($adminTemplateRenderer));

        $controllers = (new ControllerProviderLoader($modules, $services))->load();

        $router = new Router();
        $routeLoader = new ModuleRouteLoader($modules, $controllers);
        $routeLoader->registerAdminRoutes($router);
        $routeLoader->registerApiRoutes($router);

        $router->fallback(static function (Request $request) use ($pageController): Response {
            if (str_starts_with($request->path(), '/api/')) {
                return Response::json([
                    'success' => false,
                    'error' => ['code' => 'route_not_found', 'message' => 'API route not found.'],
                ], 404);
            }

            return $pageController->view($request);
        });

        return new Application($router, new SecurityHeaders($config->array('security.headers')));
    }
}
