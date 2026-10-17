<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Http;

use PDO;
use Zoosper\Auth\Http\ApiBearerAuthenticationMiddleware;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenRepository;
use Zoosper\Auth\Token\PersonalAccessTokenService;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/** @return list<array{identity:string,method:string,path:string,scope:?string,permissions:list<string>}> */
function bearerRouteMatrix(): array
{
    $root = dirname(__DIR__, 5);
    $files = array_merge(glob($root . '/app/*/config/api_routes.php') ?: [], glob($root . '/packages/*/config/api_routes.php') ?: []);
    sort($files);
    $matrix = [];
    foreach ($files as $file) {
        foreach (require $file as $route) {
            if (($route['auth'] ?? null) !== 'bearer') continue;
            $method = strtoupper((string) ($route['method'] ?? 'GET'));
            $path = (string) ($route['path'] ?? '');
            $permissions = $route['permission'] ?? [];
            if (is_string($permissions)) $permissions = [$permissions];
            $matrix[] = [
                'identity' => $method . ' ' . $path,
                'method' => $method,
                'path' => $path,
                'scope' => isset($route['scope']) && $route['scope'] !== '' ? (string) $route['scope'] : null,
                'permissions' => array_values(array_filter($permissions, 'is_string')),
            ];
        }
    }
    return $matrix;
}

function bearerMatrixPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_users(id INTEGER PRIMARY KEY,name TEXT,email TEXT,password_hash TEXT,status TEXT)');
    $pdo->exec('CREATE TABLE admin_roles(id INTEGER PRIMARY KEY,code TEXT,label TEXT)');
    $pdo->exec('CREATE TABLE admin_permissions(id INTEGER PRIMARY KEY,code TEXT,label TEXT,parent_code TEXT,sort_order INTEGER)');
    $pdo->exec('CREATE TABLE admin_role_permissions(role_id INTEGER,permission_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_user_roles(user_id INTEGER,role_id INTEGER)');
    $pdo->exec('CREATE TABLE personal_access_tokens(id INTEGER PRIMARY KEY AUTOINCREMENT,public_id TEXT,admin_user_id INTEGER,name TEXT,token_hash TEXT,scopes_json TEXT,expires_at TEXT,last_used_at TEXT,revoked_at TEXT,created_at TEXT,updated_at TEXT)');
    $pdo->exec("INSERT INTO admin_users VALUES(1,'Matrix User','matrix@test','x','active')");
    return $pdo;
}

/** @return array{0:ApiBearerAuthenticationMiddleware,1:PersonalAccessTokenService} */
function bearerMatrixRuntime(): array
{
    $pdo = bearerMatrixPdo();
    $users = new AdminUserRepository($pdo);
    $tokens = new PersonalAccessTokenRepository($pdo);
    return [new ApiBearerAuthenticationMiddleware(new PersonalAccessTokenAuthenticator($tokens, $users), new JsonResponder()), new PersonalAccessTokenService($tokens)];
}

function bearerMatrixContext(array $route): RouteContext
{
    return new RouteContext($route['method'], $route['path'], false, $route['permissions'], true, 'bearer', $route['scope']);
}

function bearerErrorCode(Response $response): ?string
{
    $value = json_decode($response->body(), true);
    return is_array($value) && is_array($value['error'] ?? null) && is_string($value['error']['code'] ?? null) ? $value['error']['code'] : null;
}

it('discovers the complete production bearer route table', function (): void {
    expect(bearerRouteMatrix())->toHaveCount(53);
});

it('blocks anonymous requests before downstream execution for every bearer route', function (): void {
    [$middleware] = bearerMatrixRuntime();
    foreach (bearerRouteMatrix() as $route) {
        $ran = false;
        $response = $middleware->process(new Request($route['method'], $route['path']), bearerMatrixContext($route), function () use (&$ran): Response { $ran = true; return Response::json([]); });
        expect($response->statusCode(), $route['identity'])->toBe(401)
            ->and(bearerErrorCode($response), $route['identity'])->toBe('invalid_bearer_token')
            ->and($ran, $route['identity'])->toBeFalse();
    }
});

it('blocks a valid token with the wrong scope for every scoped bearer route', function (): void {
    foreach (bearerRouteMatrix() as $route) {
        if ($route['scope'] === null) continue;
        [$middleware, $service] = bearerMatrixRuntime();
        $wrong = $route['scope'] === 'roles:read' ? 'pages:read' : 'roles:read';
        $issued = $service->issue(1, 'matrix', [$wrong]);
        $ran = false;
        $response = $middleware->process(new Request($route['method'], $route['path'], headers: ['authorization' => 'Bearer ' . $issued['token']]), bearerMatrixContext($route), function () use (&$ran): Response { $ran = true; return Response::json([]); });
        expect($response->statusCode(), $route['identity'])->toBe(403)
            ->and(bearerErrorCode($response), $route['identity'])->toBe('insufficient_scope')
            ->and($ran, $route['identity'])->toBeFalse();
    }
});

it('blocks a correctly scoped token whose owner lacks every declared permission', function (): void {
    foreach (bearerRouteMatrix() as $route) {
        if ($route['scope'] === null || $route['permissions'] === []) continue;
        [$middleware, $service] = bearerMatrixRuntime();
        $issued = $service->issue(1, 'matrix', [$route['scope']]);
        $ran = false;
        $response = $middleware->process(new Request($route['method'], $route['path'], headers: ['authorization' => 'Bearer ' . $issued['token']]), bearerMatrixContext($route), function () use (&$ran): Response { $ran = true; return Response::json([]); });
        expect($response->statusCode(), $route['identity'])->toBe(403)
            ->and(bearerErrorCode($response), $route['identity'])->toBe('insufficient_permission')
            ->and($ran, $route['identity'])->toBeFalse();
    }
});

it('keeps token identity as authentication only without invented scope or permission', function (): void {
    $route = array_values(array_filter(bearerRouteMatrix(), static fn (array $r): bool => $r['identity'] === 'GET /api/v1/token/me'))[0];
    expect($route['scope'])->toBeNull()->and($route['permissions'])->toBe([]);
});
