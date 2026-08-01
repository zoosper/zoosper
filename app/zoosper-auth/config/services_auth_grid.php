<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Auth\Admin\Grid\AdminUserGridPageBuilder;
use Zoosper\Auth\Admin\Grid\AuthGridPageBuilderFactory;
use Zoosper\Auth\Admin\Grid\RoleGridPageBuilder;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Grid\GridColumnRegistry;

/**
 * Auth Grid read-side service fragment.
 *
 * This map is intentionally separate until the existing Auth service manifest is
 * merged in the guarded runtime-wiring phase.
 */
return [
    AuthGridPageBuilderFactory::class => static fn (ServiceContainer $services): AuthGridPageBuilderFactory => new AuthGridPageBuilderFactory(
        pdo: $services->get(PDO::class),
        stateResolver: $services->get(GridViewStateResolver::class),
        columnRegistry: $services->has(GridColumnRegistry::class)
            ? $services->get(GridColumnRegistry::class)
            : null,
        columnOrderer: $services->has(GridColumnOrderer::class)
            ? $services->get(GridColumnOrderer::class)
            : null,
    ),
    AdminUserGridPageBuilder::class => static fn (ServiceContainer $services): AdminUserGridPageBuilder => $services
        ->get(AuthGridPageBuilderFactory::class)
        ->adminUsers(),
    RoleGridPageBuilder::class => static fn (ServiceContainer $services): RoleGridPageBuilder => $services
        ->get(AuthGridPageBuilderFactory::class)
        ->roles(),
];
