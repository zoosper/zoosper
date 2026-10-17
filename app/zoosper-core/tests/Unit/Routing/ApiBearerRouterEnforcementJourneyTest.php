<?php

declare(strict_types=1);

use Zoosper\Auth\Http\ApiBearerAuthenticationMiddleware;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenRepository;
use Zoosper\Auth\Token\PersonalAccessTokenService;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Middleware\MiddlewarePipeline;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\Router;

/** @return array{0:ApiBearerAuthenticationMiddleware,1:PersonalAccessTokenService} */
function routerBearerRuntime(): array
{
    $p = new PDO('sqlite::memory:');
    $p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $p->exec('CREATE TABLE admin_users(id INTEGER PRIMARY KEY,name TEXT,email TEXT,password_hash TEXT,status TEXT)');
    $p->exec('CREATE TABLE admin_roles(id INTEGER PRIMARY KEY,code TEXT,label TEXT)');
    $p->exec('CREATE TABLE admin_permissions(id INTEGER PRIMARY KEY,code TEXT,label TEXT,parent_code TEXT,sort_order INTEGER)');
    $p->exec('CREATE TABLE admin_role_permissions(role_id INTEGER,permission_id INTEGER)');
    $p->exec('CREATE TABLE admin_user_roles(user_id INTEGER,role_id INTEGER)');
    $p->exec('CREATE TABLE personal_access_tokens(id INTEGER PRIMARY KEY AUTOINCREMENT,public_id TEXT,admin_user_id INTEGER,name TEXT,token_hash TEXT,scopes_json TEXT,expires_at TEXT,last_used_at TEXT,revoked_at TEXT,created_at TEXT,updated_at TEXT)');
    $p->exec("INSERT INTO admin_users VALUES(1,'Router User','router@test','x','active')");
    $users = new AdminUserRepository($p); $tokens = new PersonalAccessTokenRepository($p);
    return [new ApiBearerAuthenticationMiddleware(new PersonalAccessTokenAuthenticator($tokens, $users), new JsonResponder()), new PersonalAccessTokenService($tokens)];
}

/** @return array<string,mixed> */
function productionBearerRoute(string $identity): array
{
    $root = dirname(__DIR__, 5);
    foreach (array_merge(glob($root . '/app/*/config/api_routes.php') ?: [], glob($root . '/packages/*/config/api_routes.php') ?: []) as $file) {
        foreach (require $file as $route) {
            if (strtoupper((string) ($route['method'] ?? 'GET')) . ' ' . ($route['path'] ?? '') === $identity) return $route;
        }
    }
    throw new RuntimeException('Production route not found: ' . $identity);
}

/** @param callable(Request):Response $core */
function registerProductionBearerJourney(Router $router, ApiBearerAuthenticationMiddleware $middleware, array $route, callable $core): void
{
    $permissions = $route['permission'] ?? []; if (is_string($permissions)) $permissions = [$permissions];
    $context = new RouteContext(strtoupper($route['method']), $route['path'], false, $permissions, true, 'bearer', $route['scope'] ?? null);
    $pipeline = new MiddlewarePipeline([$middleware]);
    $router->map($context->method, $context->path, static fn (Request $request): Response => $pipeline->handle($request, $context, $core), true);
}

it('denies representative Site-scoped and global object routes before lookup', function (string $identity, string $requestPath): void {
    [$middleware, $service] = routerBearerRuntime();
    $route = productionBearerRoute($identity);
    $router = new Router(); $lookups = 0;
    registerProductionBearerJourney($router, $middleware, $route, function () use (&$lookups): Response { $lookups++; return Response::json(['unexpected' => true]); });
    expect($router->dispatch(new Request($route['method'], $requestPath))->statusCode())->toBe(401)->and($lookups)->toBe(0);
    $wrong = ($route['scope'] ?? '') === 'roles:read' ? 'pages:read' : 'roles:read';
    $issued = $service->issue(1, 'wrong scope', [$wrong]);
    expect($router->dispatch(new Request($route['method'], $requestPath, headers: ['authorization' => 'Bearer ' . $issued['token']]))->statusCode())->toBe(403)->and($lookups)->toBe(0);
})->with([
    ['GET /api/v1/pages/{id:\\d+}', '/api/v1/pages/42'],
    ['GET /api/v1/roles/{id:\\d+}', '/api/v1/roles/42'],
]);
