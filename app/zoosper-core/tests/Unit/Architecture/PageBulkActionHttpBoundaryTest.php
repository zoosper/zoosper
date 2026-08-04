<?php

declare(strict_types=1);

it('keeps Page bulk HTTP activation POST-only and feature-owned', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageBulkActionController.php');
    $routes = file_get_contents($root . '/app/zoosper-page/config/admin_routes.php');
    $factories = file_get_contents($root . '/app/zoosper-page/config/controllers.php');
    expect($controller)->not->toBeFalse();
    expect($routes)->not->toBeFalse();
    expect($factories)->not->toBeFalse();
    expect($controller)->not->toContain('$_POST');
    expect($controller)->not->toContain('$_SESSION');
    expect($routes)->toContain("'method' => 'POST', 'path' => '/admin/pages/bulk-action'");
    expect($factories)->toContain('PageBulkActionController::class');
});
