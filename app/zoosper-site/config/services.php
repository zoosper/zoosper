<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Site\SiteLookupInterface;
use Zoosper\Site\Console\SiteCreateCommand;
use Zoosper\Site\Infrastructure\DatabaseSiteLookup;
use Zoosper\Site\Repository\SiteDomainRepository;
use Zoosper\Site\Repository\SiteRepository;

return [
    SiteLookupInterface::class => static function (ServiceContainer $services): DatabaseSiteLookup {
        return new DatabaseSiteLookup(
            $services->get(SiteRepository::class)
        );
    },
    SiteRepository::class => static fn (ServiceContainer $services): SiteRepository => new SiteRepository($services->get(PDO::class)),
    SiteDomainRepository::class => static fn (ServiceContainer $services): SiteDomainRepository => new SiteDomainRepository($services->get(PDO::class)),

    // Console/kernel decoupling phase: site:create console command,
    // relocated out of bin/zoosper. Discovered via config/console.php.
    SiteCreateCommand::class => static fn (ServiceContainer $services): SiteCreateCommand => new SiteCreateCommand(
        $services->get(SiteRepository::class)
    ),
];

