<?php
declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;
use Zoosper\Api\Controller\AuthController as ApiAuthController;
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
use Zoosper\Page\Controller\HomeController;

final class ApplicationFactory
{
    public static function create(string $basePath): Application
    {
        $config = ConfigRepository::fromPath($basePath . '/config');
        $pdo = (new ConnectionFactory($config, $basePath))->create();
        $users = new AdminUserRepository($pdo);
        $hasher = new PasswordHasher();
        $auth = new AuthService($users, $hasher);
        $guard = new SessionGuard($users);
        $csrf = new CsrfTokenManager();
        $json = new JsonResponder();
        $router = new Router();
        $login = new LoginController($auth, $guard, $csrf);
        $dashboard = new DashboardController($guard, $csrf);
        $apiAuth = new ApiAuthController($json, $auth, $guard);
        $router->get('/', [new HomeController(), 'index']);
        $router->get('/admin/login', [$login, 'show']);
        $router->post('/admin/login', [$login, 'login']);
        $router->post('/admin/logout', [$login, 'logout']);
        $router->get('/admin', [$dashboard, 'index']);
        $router->get('/api/v1/health', [new HealthController($json), 'show']);
        $router->get('/api/v1/hello', [new HelloController($json), 'show']);
        $router->post('/api/v1/auth/login', [$apiAuth, 'login']);
        $router->post('/api/v1/auth/logout', [$apiAuth, 'logout']);
        $router->get('/api/v1/me', [new MeController($json, $guard), 'show']);
        $router->fallback(static fn(Request $r): Response => str_starts_with($r->path(), '/api/') ? Response::json(['success' => false, 'error' => ['code' => 'route_not_found', 'message' => 'API route not found.']], 404) : Response::html('<h1>404</h1><p>Zoosper route not found.</p>', 404));
        return new Application($router, new SecurityHeaders($config->array('security.headers')));
    }
}
