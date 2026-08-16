<?php

declare(strict_types=1);

it('exposes PAT-scoped stateless Page publication and revision contracts through shared Page services', function (): void {
    $root=dirname(__DIR__,4); $routes=(string)file_get_contents($root.'/app/zoosper-api/config/api_routes.php'); $controller=(string)file_get_contents($root.'/app/zoosper-api/src/Controller/PageApiController.php'); $wiring=(string)file_get_contents($root.'/app/zoosper-api/config/controllers.php');
    foreach (['/api/v1/pages/{id:\\d+}/publish','/api/v1/pages/{id:\\d+}/unpublish','/api/v1/pages/{id:\\d+}/revisions','/api/v1/pages/{id:\\d+}/revisions/{revisionId:\\d+}/restore'] as $path) expect($routes)->toContain($path);
    expect(substr_count($routes,"'stateless' => true"))->toBeGreaterThanOrEqual(8)
        ->and($controller)->toContain("principal(\$request, 'pages:publish')")->toContain("principal(\$request, 'pages:read', true)")->toContain("principal(\$request, 'pages:write')")->toContain('$this->sitePage($request)')->toContain('capturePage($page')->toContain('page.api_revision_restored')->not->toContain('SessionGuard')
        ->and($wiring)->toContain('Application\\Publication\\PagePublicationCoordinator')->toContain('PageRevisionService::class');
});

it('keeps single Page publication orchestration outside Admin', function (): void {
    $root=dirname(__DIR__,4); expect($root.'/app/zoosper-page/src/Application/Publication/PagePublicationCoordinator.php')->toBeFile()->and($root.'/app/zoosper-page/src/Admin/Publication/PagePublicationCoordinator.php')->not->toBeFile();
});
