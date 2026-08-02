<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridBookmarkRepository;
use Zoosper\AdminGrid\GridPreferenceRepository;
use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\AdminGrid\GridViewMutationService;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\AdminGrid\GridWorkspaceMutationGuard;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Grid\GridColumnOrderer;

return [
    GridWorkspaceMutationGuard::class => static fn (ServiceContainer $services): GridWorkspaceMutationGuard => new GridWorkspaceMutationGuard(),
    GridWorkspaceMutationFormsRenderer::class => static fn (ServiceContainer $services): GridWorkspaceMutationFormsRenderer => new GridWorkspaceMutationFormsRenderer(),    GridPreferenceRepository::class => static fn (ServiceContainer $services): GridPreferenceRepository => new GridPreferenceRepository($services->get(PDO::class)),
    GridBookmarkRepository::class => static fn (ServiceContainer $services): GridBookmarkRepository => new GridBookmarkRepository($services->get(PDO::class)),
    GridStateNormaliser::class => static fn (ServiceContainer $services): GridStateNormaliser => new GridStateNormaliser(),
    GridColumnOrderer::class => static fn (ServiceContainer $services): GridColumnOrderer => new GridColumnOrderer(),
    GridViewStateResolver::class => static fn (ServiceContainer $services): GridViewStateResolver => new GridViewStateResolver(
        preferences: $services->get(GridPreferenceRepository::class),
        bookmarks: $services->get(GridBookmarkRepository::class),
        normaliser: $services->get(GridStateNormaliser::class),
        columnOrderer: $services->get(GridColumnOrderer::class),
    ),
    GridViewMutationService::class => static fn (ServiceContainer $services): GridViewMutationService => new GridViewMutationService(
        preferences: $services->get(GridPreferenceRepository::class),
        bookmarks: $services->get(GridBookmarkRepository::class),
        normaliser: $services->get(GridStateNormaliser::class),
    ),
];
