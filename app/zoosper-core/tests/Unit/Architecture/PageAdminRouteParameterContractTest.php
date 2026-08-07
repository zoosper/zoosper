<?php

declare(strict_types=1);

it('makes constrained Page path routes canonical while preserving legacy compatibility routes', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = (string) file_get_contents($root . '/app/zoosper-page/config/admin_routes.php');
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $grid = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageGridDefinition.php');

    foreach (['edit', 'preview', 'publish', 'unpublish'] as $action) {
        expect($routes)->toContain('/admin/pages/{id:\\d+}/' . $action);
    }
    expect($routes)->toContain("'/admin/pages/edit'")
        ->toContain("'/admin/pages/preview'")
        ->and($controller)->toContain("\$request->routeParam('id') ?? \$request->query('id')")
        ->and($grid)->toContain("'pages/' . \$id . '/edit'")
        ->toContain("'pages/' . \$id . '/preview'");
});
