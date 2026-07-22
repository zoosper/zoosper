<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Site\Admin\Controller\SiteAdminController;
use Zoosper\Site\Admin\Controller\SiteDomainAdminController;
use Zoosper\Site\Repository\SiteDomainRepository;
use Zoosper\Site\Repository\SiteRepository;

return [
    SiteAdminController::class => static fn (ServiceContainer $services): SiteAdminController => new SiteAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(SiteRepository::class),
        $services->get(AdminLayout::class),
    ),

    SiteDomainAdminController::class => static fn (ServiceContainer $services): SiteDomainAdminController => new SiteDomainAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(SiteDomainRepository::class),
        $services->get(SiteRepository::class),
        $services->get(AdminLayout::class),
    ),
];
