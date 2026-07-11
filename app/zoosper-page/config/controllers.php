<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\PageAdminController;
use Zoosper\Admin\Editor\ContentEditorInterface;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Message\FlashMessageStoreInterface;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Page\Admin\PageGridRepository;
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
        $services->has(AdminViewRenderer::class) ? $services->get(AdminViewRenderer::class) : null,
        new PageGridRepository($services->get(\PDO::class)),
        $services->has(HtmlSanitizerInterface::class) ? $services->get(HtmlSanitizerInterface::class) : null,
        $services->has(FlashMessageStoreInterface::class) ? $services->get(FlashMessageStoreInterface::class) : null,
        $services->has(ConfigRepository::class) ? $services->get(ConfigRepository::class) : null,
        $services->has(ContentEditorInterface::class) ? $services->get(ContentEditorInterface::class) : null,
    ),
];
