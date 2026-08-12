<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Site\Admin\Controller\SiteAdminController;
use Zoosper\Site\Admin\Controller\SiteDomainAdminController;
use Zoosper\Site\Repository\SiteDomainRepository;
use Zoosper\Site\Repository\SiteRepository;

use Zoosper\Site\Admin\Lifecycle\SiteLifecycleAdminResponder;
use Zoosper\Site\Lifecycle\SiteLifecycleCoordinator;
use Zoosper\Site\Lifecycle\SiteReferenceInspector;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Audit\AuditLoggerInterface;
return [
    SiteAdminController::class => static fn (ServiceContainer $services): SiteAdminController => new SiteAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(SiteRepository::class),
        $services->get(AdminLayout::class),
        $services->get(AdminUrlGenerator::class),
        lifecycle: new SiteLifecycleAdminResponder(
            new SiteLifecycleCoordinator(
                $services->get(PDO::class),
                $services->get(SiteRepository::class),
                new SiteReferenceInspector($services->get(PDO::class)),
                $services->has(AuditLoggerInterface::class) ? $services->get(AuditLoggerInterface::class) : null,
            ),
            $services->get(CsrfTokenManager::class),
            $services->has(FlashMessageStoreInterface::class) ? $services->get(FlashMessageStoreInterface::class) : null,
            $services->has(AdminUrlGenerator::class) ? $services->get(AdminUrlGenerator::class) : null,
        ),
    ),

    SiteDomainAdminController::class => static fn (ServiceContainer $services): SiteDomainAdminController => new SiteDomainAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(SiteDomainRepository::class),
        $services->get(SiteRepository::class),
        $services->get(AdminLayout::class),
        $services->get(AdminUrlGenerator::class),
    ),
];
