<?php

declare(strict_types=1);

use Zoosper\StoreOrders\Admin\StoreOrderAdminController;

return [[
    'method' => 'GET',
    'path' => '/admin/store-orders',
    'controller' => StoreOrderAdminController::class,
    'action' => 'index',
    'permission' => 'store_order.view',
]];
