<?php

declare(strict_types=1);

it('keeps every production bearer controller on middleware-established principal ownership', function (): void {
    $root = dirname(__DIR__, 5);
    $routeFiles = array_merge(
        glob($root . '/app/*/config/api_routes.php') ?: [],
        glob($root . '/packages/*/config/api_routes.php') ?: [],
    );
    $controllers = [];
    foreach ($routeFiles as $routeFile) {
        foreach (require $routeFile as $route) {
            if (($route['auth'] ?? null) !== 'bearer') continue;
            $controller = $route['controller'] ?? null;
            if (is_string($controller)) $controllers[$controller] = true;
        }
    }
    expect($controllers)->not->toBe([]);
    foreach (array_keys($controllers) as $controller) {
        $reflection = new ReflectionClass($controller);
        $file = $reflection->getFileName();
        expect($file, $controller)->toBeString();
        $source = (string) file_get_contents((string) $file);
        expect($source, $controller)
            ->toContain('BearerPrincipalContext')
            ->toContain('->require(')
            ->not->toContain('PersonalAccessTokenAuthenticator')
            ->not->toContain("header('authorization')")
            ->not->toContain('invalid_bearer_token')
            ->not->toContain('insufficient_scope');
    }
});
