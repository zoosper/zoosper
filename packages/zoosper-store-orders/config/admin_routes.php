<?php

declare(strict_types=1);

use Zoosper\StoreOrders\Admin\StoreOrderAdminController;
use Zoosper\StoreOrders\Admin\StoreOrderCsvExportController;

return [

    ['method' => 'GET', 'path' => '/admin/store-orders', 'controller' => StoreOrderAdminController::class, 'action' => 'index', 'permission' => 'store_order.view'],    ['method' => 'GET', 'path' => '/admin/store-orders/export', 'controller' => StoreOrderCsvExportController::class, 'action' => 'export', 'permission' => 'store_order.export'],
    ['method' => 'POST', 'path' => '/admin/store-orders', 'controller' => StoreOrderAdminController::class, 'action' => 'mutate', 'permission' => 'store_order.view'],
];











