<?php

declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Controller\AuditLogController;
use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;
use Zoosper\Admin\Controller\LoginHistoryController;
use Zoosper\Admin\Controller\PageAdminController;
use Zoosper\Admin\Controller\RoleAdminController;
use Zoosper\Admin\Controller\UserAdminController;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Api\Controller\AuthController as ApiAuthController;
use Zoosper\Api\Controller\ContentPageController;
use Zoosper\Api\Controller\HealthController;
use Zoosper\Api\Controller\HelloController;
use Zoosper\Api\Controller\MeController;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Database\ConnectionFactory;
use Zoosper\Core\Http\Application;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Routing\ModuleRouteLoader;
use Zoosper\Core\Routing\Router;
use Zoosper\Core\Security\SecurityHeaders;
use Zoosper\Page\Controller\PageController;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Site\Service\SiteResolver;

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

        $passwordHasher = new PasswordHasher();
        $auth = new AuthService($userRepository, $passwordHasher);
        $guard = new SessionGuard($userRepository);
        $csrf = new CsrfTokenManager();
        $json = new JsonResponder();
        $loginHistoryRepository = new LoginHistoryRepository($pdo);
        $auditLogRepository = new AuditLogRepository($pdo);
        $auditLogger = new AuditLogger($auditLogRepository);

        $adminMenu = new AdminMenu(new AdminMenuLoader($modules));
        $adminLayout = new AdminLayout($adminMenu, $config);

        $siteResolver = new SiteResolver($siteRepository);
        $pageRenderer = new PageRenderer();

        $loginController = new LoginController($auth, $guard, $csrf, $loginHistoryRepository);
        $auditLogController = new AuditLogController($guard, $auditLogRepository, $adminLayout);
        $loginHistoryController = new LoginHistoryController($guard, $loginHistoryRepository, $adminLayout);
        $dashboardController = new DashboardController($guard, $csrf, $adminLayout);
        $pageAdminController = new PageAdminController($guard, $csrf, $pageRepository, $siteRepository, $pageRenderer, $adminLayout);
        $userAdminController = new UserAdminController($guard, $csrf, $userRepository, $roleRepository, $passwordHasher, $adminLayout);
        $roleAdminController = new RoleAdminController(
            $guard,
            $csrf,
            $roleRepository,
            $adminLayout,
            $userRepository,
            $auditLogger,
        );
        $apiAuthController = new ApiAuthController($json, $auth, $guard);
        $pageController = new PageController($siteResolver, $pageRepository, $pageRenderer);
        $contentPageController = new ContentPageController($json, $siteResolver, $pageRepository);
        $healthController = new HealthController($json);
        $helloController = new HelloController($json);
        $meController = new MeController($json, $guard);

        $controllers = [
            LoginController::class => $loginController,
            DashboardController::class => $dashboardController,
            PageAdminController::class => $pageAdminController,
            UserAdminController::class => $userAdminController,
            RoleAdminController::class => $roleAdminController,
            ApiAuthController::class => $apiAuthController,
            HealthController::class => $healthController,
            HelloController::class => $helloController,
            MeController::class => $meController,
            ContentPageController::class => $contentPageController,
            AuditLogController::class => $auditLogController,
            LoginHistoryController::class => $loginHistoryController,
        ];

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
