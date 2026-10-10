<?php

declare(strict_types=1);

use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;

it('keeps every non-anonymous API controller behind session or bearer authorization', function (): void {
    $root = dirname(__DIR__, 5);
    $routeFiles = array_merge(
        glob($root . '/app/*/config/api_routes.php') ?: [],
        glob($root . '/packages/*/config/api_routes.php') ?: [],
    );
    sort($routeFiles);

    $anonymous = [
        'GET /api/v1/health',
        'GET /api/v1/hello',
        'POST /api/v1/auth/login',
        'GET /api/v1/content/page',
        'GET /api/v1/menu',
        'GET /robots.txt',
        'GET /sitemap.xml',
    ];
    $session = [
        'POST /api/v1/auth/logout',
        'GET /api/v1/me',
    ];
    $bearerIdentity = 'GET /api/v1/token/me';
    $failures = [];

    foreach ($routeFiles as $routeFile) {
        $routes = require $routeFile;
        expect($routes)->toBeArray();

        foreach ($routes as $route) {
            expect($route)->toBeArray();
            $method = strtoupper((string) ($route['method'] ?? 'GET'));
            $path = (string) ($route['path'] ?? '');
            $identity = $method . ' ' . $path;
            $controller = $route['controller'] ?? null;

            if (!is_string($controller) || !class_exists($controller)) {
                $failures[] = $identity . ': missing controller';
                continue;
            }

            $reflection = new ReflectionClass($controller);
            $constructor = $reflection->getConstructor();
            $dependencies = [];
            foreach ($constructor?->getParameters() ?? [] as $parameter) {
                $type = $parameter->getType();
                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $dependencies[] = $type->getName();
                }
            }

            if (in_array($identity, $anonymous, true)) {
                continue;
            }
            if (in_array($identity, $session, true)) {
                if (!in_array(SessionGuard::class, $dependencies, true)) {
                    $failures[] = $identity . ': missing session authorization owner';
                }
                continue;
            }
            if ($identity === $bearerIdentity || str_contains($path, '/api/v1/')) {
                if (!in_array(PersonalAccessTokenAuthenticator::class, $dependencies, true)) {
                    $failures[] = $identity . ': missing bearer authorization owner';
                }
                continue;
            }

            $failures[] = $identity . ': unclassified API route';
        }
    }

    expect($failures)->toBe([]);
});
