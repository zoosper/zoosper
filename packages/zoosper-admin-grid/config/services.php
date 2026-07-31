<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridBookmarkRepository;
use Zoosper\AdminGrid\GridPreferenceRepository;
use Zoosper\Core\Container\ServiceContainer;

return [
    GridPreferenceRepository::class => static fn (ServiceContainer $services): GridPreferenceRepository => new GridPreferenceRepository($services->get(PDO::class)),
    GridBookmarkRepository::class => static fn (ServiceContainer $services): GridBookmarkRepository => new GridBookmarkRepository($services->get(PDO::class)),
];
