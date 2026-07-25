<?php

declare(strict_types=1);

use Zoosper\Core\App\CmsVersion;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Routing\FallbackHandlerInterface;
use Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer;
use Zoosper\Page\Content\BlockJsonToHtmlRenderer;
use Zoosper\Page\Controller\PageController;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Routing\PageFallbackHandler;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

return [
    PageRepository::class => static fn (ServiceContainer $services): PageRepository => new PageRepository($services->get(PDO::class)),
    BlockJsonToHtmlRenderer::class => static fn (ServiceContainer $services): BlockJsonToHtmlRenderer => new BlockJsonToHtmlRenderer(
        $services->has(EditorJsImageBlockSanitizer::class) ? $services->get(EditorJsImageBlockSanitizer::class) : null,
    ),
    PageRenderer::class => static fn (ServiceContainer $services): PageRenderer => new PageRenderer(
        $services->get('theme.frontend_template_renderer'),
        $services->get(CmsVersion::class),
        $services->get(ModuleRegistry::class),
        $services->get(BlockJsonToHtmlRenderer::class),
    ),
    PageController::class => static fn (ServiceContainer $services): PageController => new PageController(
        $services->get(SiteRepository::class),
        $services->get(PageRepository::class),
        $services->get(PageRenderer::class),
    ),

    // Phase 1.93: register the page module as the frontend fallback handler so
    // ApplicationFactory resolves FallbackHandlerInterface instead of falling
    // back to NullFallbackHandler. This is what makes frontend page views work.
    FallbackHandlerInterface::class => static fn (ServiceContainer $services): FallbackHandlerInterface
        => new PageFallbackHandler($services->get(PageController::class)),
];
