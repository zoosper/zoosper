<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridViewMutationService;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\AdminGrid\GridWorkspaceMutationGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\StoreOrders\Admin\StoreOrderGridMutationCoordinator;
use Zoosper\StoreOrders\Admin\StoreOrderGridMutationHandler;
use Zoosper\StoreOrders\Admin\StoreOrderGridWorkspace;

return [
    StoreOrderGridWorkspace::class => static fn (ServiceContainer $services): StoreOrderGridWorkspace => new StoreOrderGridWorkspace(
        resolver: $services->get(GridViewStateResolver::class),
        columnOrderer: $services->get(GridColumnOrderer::class),
        adminUrls: $services->get(AdminUrlGenerator::class),
    ),
    StoreOrderGridMutationHandler::class => static fn (ServiceContainer $services): StoreOrderGridMutationHandler => new StoreOrderGridMutationHandler(
        mutations: $services->get(GridViewMutationService::class),
    ),
    StoreOrderGridMutationCoordinator::class => static fn (ServiceContainer $services): StoreOrderGridMutationCoordinator => new StoreOrderGridMutationCoordinator(
        handler: $services->get(StoreOrderGridMutationHandler::class),
        guard: $services->get(GridWorkspaceMutationGuard::class),
    ),
];











