<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use Zoosper\StoreOrders\Admin\StoreOrderAdminController;
use Zoosper\StoreOrders\Admin\StoreOrderGridWorkspace;

it('registers the workspace as a service rather than a controller', function (): void {
    $root = dirname(__DIR__, 4);
    $services = require $root . '/packages/zoosper-store-orders/config/services.php';
    $controllers = require $root . '/packages/zoosper-store-orders/config/controllers.php';

    expect($services)->toHaveKey(StoreOrderGridWorkspace::class)
        ->and($controllers)->toHaveKey(StoreOrderAdminController::class)
        ->and($controllers)->not->toHaveKey(StoreOrderGridWorkspace::class);
});
