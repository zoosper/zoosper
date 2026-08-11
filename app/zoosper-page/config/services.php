<?php

declare(strict_types=1);

use Marko\View\ViewInterface;
use Zoosper\Page\Repository\PageRevisionRepository;
use Zoosper\Page\Service\PageRevisionService;

use Marko\Cache\Contracts\CacheInterface;
use Zoosper\Core\App\CmsVersion;
use Zoosper\Core\Cache\CacheKeyBuilder;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Routing\CachingFallbackHandler;
use Zoosper\Core\Routing\FallbackHandlerInterface;
use Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer;
use Zoosper\Page\Content\BlockJsonToHtmlRenderer;
use Zoosper\Page\Contract\FrontendNavigationContributorInterface;
use Zoosper\Page\Controller\PageController;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Routing\PageFallbackHandler;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Page\Console\PageCreateCommand;

return [
    PageRevisionRepository::class => static fn ($services): PageRevisionRepository => new PageRevisionRepository($services->get(\PDO::class)),
    PageRevisionService::class => static fn ($services): PageRevisionService => new PageRevisionService($services->get(PageRevisionRepository::class), (int) ($services->get(\Zoosper\Core\Config\ConfigRepository::class)->get('page_revisions.retention', 50))),
    PageRepository::class => static fn (ServiceContainer $services): PageRepository => new PageRepository($services->get(PDO::class)),
    BlockJsonToHtmlRenderer::class => static fn (ServiceContainer $services): BlockJsonToHtmlRenderer => new BlockJsonToHtmlRenderer(
        $services->has(EditorJsImageBlockSanitizer::class) ? $services->get(EditorJsImageBlockSanitizer::class) : null,
    ),
    PageRenderer::class => static fn (ServiceContainer $services): PageRenderer => new PageRenderer(
        $services->get('theme.frontend_template_renderer'),
        $services->get(CmsVersion::class),
        $services->get(ModuleRegistry::class),
        $services->get(BlockJsonToHtmlRenderer::class),
        $services->has(FrontendNavigationContributorInterface::class)
            ? $services->get(FrontendNavigationContributorInterface::class)
            : null,
        $services->has(ViewInterface::class)
            ? $services->get(ViewInterface::class)
            : null,
    ),
    PageController::class => static fn (ServiceContainer $services): PageController => new PageController(
        $services->get(SiteRepository::class),
        $services->get(PageRepository::class),
        $services->get(PageRenderer::class),
    ),
    PageCreateCommand::class => static fn (ServiceContainer $services): PageCreateCommand => new PageCreateCommand(
        $services->get(SiteRepository::class),
        $services->get(\Zoosper\Page\Repository\PageRepository::class), // use however PageRepository is already registered/imported in this file
    ),

    // Phase 1.93: register the page module as the frontend fallback handler so
    // ApplicationFactory resolves FallbackHandlerInterface instead of falling
    // back to NullFallbackHandler. This is what makes frontend page views work.
    //
    // PAGE CACHE (new, 2026-07-31): the real PageFallbackHandler is now
    // wrapped by CachingFallbackHandler — a generic decorator owned by
    // zoosper-core (see that class's own docblock for the full safety
    // model and honestly-stated limitations). Disabled by default via
    // config/page_cache.php; when disabled, CachingFallbackHandler
    // delegates every call straight through to PageFallbackHandler with
    // zero behavioural change, so this wrapping is safe to ship even
    // before the feature is ever turned on.
    FallbackHandlerInterface::class => static function (ServiceContainer $services): FallbackHandlerInterface {
        $pageFallbackHandler = new PageFallbackHandler($services->get(PageController::class));
        $pageCacheConfig = $services->get(ConfigRepository::class)->array('page_cache');

        return new CachingFallbackHandler(
            inner: $pageFallbackHandler,
            cache: $services->get(CacheInterface::class),
            keyBuilder: $services->get(CacheKeyBuilder::class),
            enabled: (bool) ($pageCacheConfig['enabled'] ?? false),
            ttlSeconds: (int) ($pageCacheConfig['ttl'] ?? 300),
        );
    },
];
