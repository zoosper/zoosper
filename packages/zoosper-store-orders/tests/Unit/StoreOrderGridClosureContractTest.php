<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use Zoosper\StoreOrders\Admin\StoreOrderAdminController;
use Zoosper\StoreOrders\Admin\StoreOrderGridMutationCoordinator;
use Zoosper\StoreOrders\Admin\StoreOrderGridMutationHandler;

it('closes Store Orders workspace persistence through CSRF protected POST coordination', function (): void {
    $root = dirname(__DIR__, 4);
    $routes = require $root . '/packages/zoosper-store-orders/config/admin_routes.php';
    $services = require $root . '/packages/zoosper-store-orders/config/services.php';
    $controllers = require $root . '/packages/zoosper-store-orders/config/controllers.php';
    $source = file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderAdminController.php',
    );

    expect($routes)->toContain([
        'method' => 'POST',
        'path' => '/admin/store-orders',
        'controller' => StoreOrderAdminController::class,
        'action' => 'mutate',
        'permission' => 'store_order.view',
    ])->and($services)->toHaveKeys([
        StoreOrderGridMutationHandler::class,
        StoreOrderGridMutationCoordinator::class,
    ])->and($controllers)->toHaveKey(StoreOrderAdminController::class)
        ->and($source)->not->toBeFalse()
        ->and($source)->toContain("'_csrf_token'")
        ->and($source)->toContain('GridWorkspaceMutationFormsRenderer')
        ->and($source)->toContain("new GridWorkspaceRequest('POST'");
});

it('keeps Store Orders mutation identity server owned', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderGridMutationHandler.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->not->toContain('$post[\'admin_user_id\']')
        ->and($source)->toContain('StoreOrderGridWorkspace::GRID_KEY')
        ->and($source)->toContain('GridWorkspacePostState::fromPost($post)');
});











