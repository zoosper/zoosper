<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\StoreOrders\Admin\StoreOrderGridWorkspace;

return [
    StoreOrderGridWorkspace::class => static fn (ServiceContainer $services): StoreOrderGridWorkspace => new StoreOrderGridWorkspace(
        resolver: $services->get(GridViewStateResolver::class),
        columnOrderer: $services->get(GridColumnOrderer::class),
    ),
];
