<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Site\Service\SiteResolver;

return [
    SiteRepository::class => static fn (ServiceContainer $services): SiteRepository => new SiteRepository($services->get(PDO::class)),
    SiteResolver::class => static fn (ServiceContainer $services): SiteResolver => new SiteResolver($services->get(SiteRepository::class)),
];
