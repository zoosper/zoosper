<?php

declare(strict_types=1);

it('owns bearer-protected stateless Media reads upload and lifecycle routes', function (): void {
    $root = dirname(__DIR__, 3);
    $routes = require $root . '/config/api_routes.php';
    $paths = array_column($routes, 'path');
    foreach ([
        '/api/v1/media',
        '/api/v1/media/{id:\d+}',
        '/api/v1/media/{id:\d+}/derivatives',
        '/api/v1/media/{id:\d+}/archive',
        '/api/v1/media/{id:\d+}/restore',
    ] as $path) {
        expect($paths)->toContain($path);
    }

    $scopes = [];
    foreach ($routes as $route) {
        expect($route['auth'])->toBe('bearer')
            ->and($route['public'])->toBeFalse()
            ->and($route['stateless'])->toBeTrue();
        $scope = $route['scope'] ?? null;
        if (is_string($scope)) {
            $scopes[$scope] = true;
        }
    }
    expect(array_keys($scopes))
        ->toContain('media:read')
        ->toContain('media:upload')
        ->toContain('media:delete');

    $controller = (string) file_get_contents($root . '/src/Api/MediaApiController.php');
    expect($controller)
        ->toContain('BearerPrincipalContext')
        ->toContain('$this->principals->require($request)')
        ->toContain('MediaUploadService')
        ->toContain('MediaLifecycleCoordinator')
        ->not->toContain('PersonalAccessTokenAuthenticator')
        ->not->toContain("header('authorization')");
});

it('exposes permanent deletion only through shared lifecycle', function (): void {
    expect((string) file_get_contents(dirname(__DIR__, 3) . '/src/Api/MediaApiController.php'))
        ->toContain('deletePermanently');
});
