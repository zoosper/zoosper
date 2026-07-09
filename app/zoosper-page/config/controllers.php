<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\PageAdminController;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

return [
    PageAdminController::class => static fn (ServiceContainer $services): PageAdminController => new PageAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(PageRepository::class),
        $services->get(SiteRepository::class),
        $services->get(PageRenderer::class),
        $services->get(AdminLayout::class),
    ),
];
