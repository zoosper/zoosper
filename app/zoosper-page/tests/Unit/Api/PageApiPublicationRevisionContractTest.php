<?php

declare(strict_types=1);

use Zoosper\Page\Api\PageApiController;

it('exposes bearer-protected Page publication and revision routes', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-page/config/api_routes.php';
    $expected = [
        '/api/v1/pages/{id:\d+}/publish',
        '/api/v1/pages/{id:\d+}/unpublish',
        '/api/v1/pages/{id:\d+}/revisions',
        '/api/v1/pages/{id:\d+}/revisions/{revisionId:\d+}/restore',
    ];
    $protected = array_values(array_filter(
        $routes,
        static fn (array $route): bool => ($route['controller'] ?? null) === PageApiController::class
            && in_array($route['path'] ?? '', $expected, true),
    ));
    expect(array_column($protected, 'path'))->toEqualCanonicalizing($expected);
    foreach ($protected as $route) {
        expect($route['auth'])->toBe('bearer')
            ->and($route['public'])->toBeFalse()
            ->and($route['stateless'])->toBeTrue();
    }
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Api/PageApiController.php');
    expect($controller)->toContain("principal(\$request, 'pages:publish')")
        ->toContain('capturePage($page')
        ->toContain('page.api_revision_restored')
        ->not->toContain('SessionGuard');
});

it('keeps single Page publication orchestration outside Admin', function (): void {
    expect((string) file_get_contents(dirname(__DIR__, 5) . '/app/zoosper-page/src/Api/PageApiController.php'))
        ->toContain('PagePublicationCoordinator');
});
