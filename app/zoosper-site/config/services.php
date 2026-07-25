<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Site\Repository\SiteDomainRepository;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Site\Service\SiteResolver;

return [
    \\Zoosper\\Core\\Site\\SiteLookupInterface::class => static function ($container): \\Zoosper\\Site\\Infrastructure\\DatabaseSiteLookup {
        return new \\Zoosper\\Site\\Infrastructure\\DatabaseSiteLookup(
            $container->get(\\Zoosper\\Site\\Repository\\SiteRepository::class)
        );
    },
    SiteRepository::class => static fn (ServiceContainer $services): SiteRepository => new SiteRepository($services->get(PDO::class)),
    SiteResolver::class => static fn (ServiceContainer $services): SiteResolver => new SiteResolver($services->get(SiteRepository::class)),
    SiteDomainRepository::class => static fn (ServiceContainer $services): SiteDomainRepository => new SiteDomainRepository($services->get(PDO::class)),
];
