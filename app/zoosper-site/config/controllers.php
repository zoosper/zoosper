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
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Site\Api\SiteApiController;
use Zoosper\Site\Application\SiteMutationService;
return [
    SiteApiController::class => static fn (ServiceContainer $s): SiteApiController => new SiteApiController($s->get(JsonResponder::class),$s->get(PersonalAccessTokenAuthenticator::class),$s->get(SiteRepository::class),$s->get(SiteLifecycleCoordinator::class),$s->get(SiteMutationService::class)),

    SiteAdminController::class => static fn (ServiceContainer $services): SiteAdminController => new SiteAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(SiteRepository::class),
        $services->get(AdminLayout::class),
        $services->get(AdminUrlGenerator::class),
        lifecycle: new SiteLifecycleAdminResponder(
            $services->get(SiteLifecycleCoordinator::class),
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
