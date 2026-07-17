<?php

declare(strict_types=1);

use Zoosper\Core\App\CmsVersion;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Page\Controller\PageController;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

return [
    PageRepository::class => static fn (ServiceContainer $services): PageRepository => new PageRepository($services->get(PDO::class)),
    PageRenderer::class => static fn (ServiceContainer $services): PageRenderer => new PageRenderer(
        $services->get('theme.frontend_template_renderer'),
        $services->get(CmsVersion::class),
        $services->get(ModuleRegistry::class),
    ),
    PageController::class => static fn (ServiceContainer $services): PageController => new PageController(
        $services->get(SiteRepository::class),
        $services->get(PageRepository::class),
        $services->get(PageRenderer::class),
    ),
];
