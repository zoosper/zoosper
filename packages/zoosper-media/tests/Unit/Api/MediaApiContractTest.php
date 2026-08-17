<?php

declare(strict_types=1);

it('owns stateless PAT Media reads upload and reversible lifecycle routes', function (): void {
    $root = dirname(__DIR__, 3);
    $routes = (string) file_get_contents($root . '/config/api_routes.php');
    $controller = (string) file_get_contents($root . '/src/Api/MediaApiController.php');
    foreach (['/api/v1/media', '/api/v1/media/{id:\\d+}', '/api/v1/media/{id:\\d+}/derivatives', '/api/v1/media/{id:\\d+}/archive', '/api/v1/media/{id:\\d+}/restore'] as $path) {
        expect($routes)->toContain($path);
    }
    expect(substr_count($routes, "'stateless' => true"))->toBe(6)
        ->and($controller)->toContain("'media:read'")->toContain("'media:upload'")->toContain("'media:delete'")
        ->toContain("can('media.manage')")->toContain('MediaUploadService')->toContain('MediaLifecycleCoordinator')
        ->not->toContain("'storage_path'")->not->toContain('tokenHash')->not->toContain('SessionGuard');
});

it('does not expose permanent deletion before reference safety exists', function (): void {
    $root = dirname(__DIR__, 3);
    $routes = (string) file_get_contents($root . '/config/api_routes.php');
    expect($routes)->not->toContain("'method' => 'DELETE'");
});
