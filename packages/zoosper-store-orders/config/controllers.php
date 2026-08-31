<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\AdminGrid\GridWorkspaceCsvExportService;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\GridHtmlRenderer;
use Zoosper\StoreOrders\Admin\StoreOrderAdminController;
use Zoosper\StoreOrders\Admin\StoreOrderCsvExportController;
use Zoosper\StoreOrders\Admin\StoreOrderGridMutationCoordinator;
use Zoosper\StoreOrders\Admin\StoreOrderGridWorkspace;
use Zoosper\StoreOrders\StoreOrderDataSourceFactory;

return [
    StoreOrderCsvExportController::class => static fn (ServiceContainer $services): StoreOrderCsvExportController => new StoreOrderCsvExportController(
        guard: $services->get(SessionGuard::class),
        workspace: $services->get(StoreOrderGridWorkspace::class),
        dataSources: new StoreOrderDataSourceFactory(),
        exports: $services->get(GridWorkspaceCsvExportService::class),
        config: $services->get(ConfigRepository::class)->array('store_orders'),
        adminUrls: $services->get(AdminUrlGenerator::class),
    ),    StoreOrderAdminController::class => static fn (ServiceContainer $services): StoreOrderAdminController => new StoreOrderAdminController(
        guard: $services->get(SessionGuard::class),
        csrf: $services->get(CsrfTokenManager::class),
        layout: $services->get(AdminLayoutRendererInterface::class),
        dataSources: new StoreOrderDataSourceFactory(),
        workspace: $services->get(StoreOrderGridWorkspace::class),
        mutations: $services->get(StoreOrderGridMutationCoordinator::class),
        mutationForms: $services->get(GridWorkspaceMutationFormsRenderer::class),
        config: $services->get(ConfigRepository::class)->array('store_orders'),
        gridRenderer: new GridHtmlRenderer(),
        adminUrls: $services->get(AdminUrlGenerator::class),
    ),
];











