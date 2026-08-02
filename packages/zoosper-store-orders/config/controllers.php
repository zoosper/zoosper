<?php

declare(strict_types=1);

use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\StoreOrders\Admin\StoreOrderAdminController;
use Zoosper\StoreOrders\Admin\StoreOrderGridWorkspace;
use Zoosper\StoreOrders\StoreOrderDataSourceFactory;

return [
    StoreOrderAdminController::class => static fn (ServiceContainer $services): StoreOrderAdminController => new StoreOrderAdminController(
        guard: $services->get(SessionGuard::class),
        layout: $services->get(AdminLayoutRendererInterface::class),
        dataSources: new StoreOrderDataSourceFactory(),
        workspace: $services->get(StoreOrderGridWorkspace::class),
        config: $services->get(ConfigRepository::class)->array('store_orders'),
    ),
];
