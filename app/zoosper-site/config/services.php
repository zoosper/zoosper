<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Site\Application\SiteMutationService;
use Zoosper\Site\Lifecycle\SiteLifecycleCoordinator;
use Zoosper\Site\Lifecycle\SiteReferenceInspector;

use Zoosper\Core\Site\SiteLookupInterface;
use Zoosper\Site\Console\SiteCreateCommand;
use Zoosper\Site\Infrastructure\DatabaseSiteLookup;
use Zoosper\Site\Repository\SiteDomainRepository;
use Zoosper\Site\Repository\SiteRepository;

use Zoosper\Site\Admin\Grid\{SiteGrid,SiteDomainGrid};
return [
    SiteGrid::class => static fn(ServiceContainer $s): SiteGrid => new SiteGrid($s->get(PDO::class),$s->get(AdminUrlGenerator::class)),
    SiteDomainGrid::class => static fn(ServiceContainer $s): SiteDomainGrid => new SiteDomainGrid($s->get(PDO::class),$s->get(AdminUrlGenerator::class)),

    SiteReferenceInspector::class => static fn (ServiceContainer $services): SiteReferenceInspector => new SiteReferenceInspector($services->get(PDO::class)),
    SiteLifecycleCoordinator::class => static fn (ServiceContainer $services): SiteLifecycleCoordinator => new SiteLifecycleCoordinator($services->get(PDO::class),$services->get(SiteRepository::class),$services->get(SiteReferenceInspector::class),$services->has(AuditLoggerInterface::class)?$services->get(AuditLoggerInterface::class):null),
    SiteMutationService::class => static fn (ServiceContainer $services): SiteMutationService => new SiteMutationService($services->get(PDO::class),$services->get(SiteRepository::class),$services->has(AuditLoggerInterface::class)?$services->get(AuditLoggerInterface::class):null),
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











