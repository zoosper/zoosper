<?php

declare(strict_types=1);

it('owns stateless PAT Media reads upload and reversible lifecycle routes', function (): void {
    $root = dirname(__DIR__, 3);
    $routes = (string) file_get_contents($root . '/config/api_routes.php');
    $controller = (string) file_get_contents($root . '/src/Api/MediaApiController.php');
    foreach (['/api/v1/media', '/api/v1/media/{id:\d+}', '/api/v1/media/{id:\d+}/derivatives', '/api/v1/media/{id:\d+}/archive', '/api/v1/media/{id:\d+}/restore'] as $path) {
        expect($routes)->toContain($path);
    }
    expect(substr_count($routes, "'stateless' => true"))->toBe(7)
        ->and($controller)->toContain("'media:read'")->toContain("'media:upload'")->toContain("'media:delete'")
        ->toContain("can('media.manage')")->toContain('MediaUploadService')->toContain('MediaLifecycleCoordinator')
        ->toContain('MediaApiReadQuery::fromRequest($request)')
        ->toContain("'pagination' => \$this->normalisePagination(\$result)")
        ->not->toContain("'storage_path'")->not->toContain('tokenHash')->not->toContain('SessionGuard');
});

it('exposes permanent deletion only through the shared reference-safe lifecycle', function (): void {
    $root = dirname(__DIR__, 3);
    $routes = (string) file_get_contents($root . '/config/api_routes.php');
    $controller = (string) file_get_contents($root . '/src/Api/MediaApiController.php');
    expect($routes)->toContain("'method' => 'DELETE'")->and($controller)->toContain('deletePermanentlyGuarded')->toContain("'blockers' => \$result->blockers");
});











