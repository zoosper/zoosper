<?php

declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;
use Zoosper\Admin\Controller\PageAdminController;
use Zoosper\Api\Controller\AuthController as ApiAuthController;
use Zoosper\Api\Controller\ContentPageController;
use Zoosper\Api\Controller\HealthController;
use Zoosper\Api\Controller\HelloController;
use Zoosper\Api\Controller\MeController;
use Zoosper\Auth\Repository\AdminUserRepository;
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

        $userRepository = new AdminUserRepository($pdo);
        $siteRepository = new SiteRepository($pdo);
        $pageRepository = new PageRepository($pdo);

        $passwordHasher = new PasswordHasher();
        $auth = new AuthService($userRepository, $passwordHasher);
        $guard = new SessionGuard($userRepository);
        $csrf = new CsrfTokenManager();
        $json = new JsonResponder();

        $siteResolver = new SiteResolver($siteRepository);
        $pageRenderer = new PageRenderer();

        $loginController = new LoginController($auth, $guard, $csrf);
        $dashboardController = new DashboardController($guard, $csrf);
        $pageAdminController = new PageAdminController(
            guard: $guard,
            csrf: $csrf,
            pages: $pageRepository,
            sites: $siteRepository,
            renderer: $pageRenderer,
        );
        $apiAuthController = new ApiAuthController($json, $auth, $guard);
        $pageController = new PageController($siteResolver, $pageRepository, $pageRenderer);
        $contentPageController = new ContentPageController($json, $siteResolver, $pageRepository);

        $router = new Router();

        $router->get('/admin/login', [$loginController, 'show']);
        $router->post('/admin/login', [$loginController, 'login']);
        $router->post('/admin/logout', [$loginController, 'logout']);
        $router->get('/admin', [$dashboardController, 'index']);

        $router->get('/admin/pages', [$pageAdminController, 'index']);
        $router->get('/admin/pages/create', [$pageAdminController, 'createForm']);
        $router->post('/admin/pages/create', [$pageAdminController, 'create']);
        $router->get('/admin/pages/edit', [$pageAdminController, 'editForm']);
        $router->post('/admin/pages/edit', [$pageAdminController, 'update']);
        $router->get('/admin/pages/preview', [$pageAdminController, 'preview']);
        $router->post('/admin/pages/publish', [$pageAdminController, 'publish']);
        $router->post('/admin/pages/unpublish', [$pageAdminController, 'unpublish']);

        $router->get('/api/v1/health', [new HealthController($json), 'show']);
        $router->get('/api/v1/hello', [new HelloController($json), 'show']);
        $router->post('/api/v1/auth/login', [$apiAuthController, 'login']);
        $router->post('/api/v1/auth/logout', [$apiAuthController, 'logout']);
        $router->get('/api/v1/me', [new MeController($json, $guard), 'show']);
        $router->get('/api/v1/content/page', [$contentPageController, 'show']);

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
            router: $router,
            securityHeaders: new SecurityHeaders($config->array('security.headers')),
        );
    }
}
