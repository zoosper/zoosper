<?php

declare(strict_types=1);

use Marko\View\ViewInterface;
use Zoosper\Page\Repository\PageRevisionRepository;
use Zoosper\Page\Application\Save\PageSaveCoordinator;
use Zoosper\AdminForm\AdminFormProcessorConfigFactory;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Core\Error\ErrorHandler;
use Zoosper\Page\Lifecycle\PageLifecycleCoordinator;
use Zoosper\Page\Lifecycle\PageReferenceInspector;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Page\Service\PageRevisionService;
use Zoosper\Page\Console\StarterSiteInstallCommand;
use Zoosper\Page\Seo\PageSeoContributor;
use Zoosper\Page\Seo\PageSitemapContributor;
use Zoosper\Seo\Metadata\SeoMetadataManager;

use Zoosper\Cache\Contract\CacheInterface;
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

use Zoosper\Page\Admin\PageGridDataSource;
use Zoosper\Page\Admin\PageGridDefinition;
use Zoosper\Page\Admin\PageGridRepository;
use Zoosper\Page\Admin\PageGridWorkspace;
use Zoosper\Page\Admin\PageAdminGridResponder;
use Zoosper\Page\Admin\PageGridSiteFilter;
use Zoosper\Page\Admin\PageSiteFilterOptions;
use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Grid\GridHtmlRenderer;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\AdminGrid\GridBulkActionManifestRenderer;
use Zoosper\Page\Admin\PageGridMutationCoordinator;
use Zoosper\Page\Admin\PageGridMutationHandler;
use Zoosper\AdminGrid\GridWorkspaceMutationGuard;
use Zoosper\AdminGrid\GridViewMutationService;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Grid\GridColumnRegistry;

return [
    PageAdminGridResponder::class => static function (ServiceContainer $services): PageAdminGridResponder {
        $definition = new PageGridDefinition(
            $services->has(GridColumnRegistry::class) ? $services->get(GridColumnRegistry::class) : null,
            new PageGridSiteFilter(new PageSiteFilterOptions($services->get(SiteRepository::class))),
            $services->get(AdminUrlGenerator::class),
        );

        return new PageAdminGridResponder(
            pages: $services->get(PageRepository::class),
            sites: $services->get(SiteRepository::class),
            views: $services->get(AdminViewRendererInterface::class),
            csrf: $services->get(CsrfTokenManager::class),
            definition: $definition,
            dataSource: new PageGridDataSource(new PageGridRepository($services->get(PDO::class))),
            gridRenderer: new GridHtmlRenderer(),
            workspace: new PageGridWorkspace(
                $definition,
                $services->get(GridViewStateResolver::class),
                new GridCompactWorkspaceRenderer(),
                adminUrls: $services->get(AdminUrlGenerator::class),
            ),
            mutationForms: $services->get(GridWorkspaceMutationFormsRenderer::class),
            bulkManifest: new GridBulkActionManifestRenderer(),
            mutations: new PageGridMutationCoordinator(
                new PageGridMutationHandler(
                    $definition,
                    $services->get(GridViewMutationService::class),
                ),
                $services->get(GridWorkspaceMutationGuard::class),
            ),
            flashMessages: $services->has(FlashMessageStoreInterface::class) ? $services->get(FlashMessageStoreInterface::class) : null,
            adminUrls: $services->get(AdminUrlGenerator::class),
        );
    },
    PageSaveCoordinator::class => static fn (ServiceContainer $services): PageSaveCoordinator => new PageSaveCoordinator(
        $services->get(PageRepository::class),
        $services->get(HtmlSanitizerInterface::class),
        $services->has(ConfigRepository::class) ? $services->get(ConfigRepository::class) : null,
        $services->has(AdminFormProcessorConfigFactory::class)
            ? $services->get(AdminFormProcessorConfigFactory::class)->create($services->has(ConfigRepository::class) ? $services->get(ConfigRepository::class)->array('admin_forms') : [])
            : null,
        $services->has(EntitySaveLifecycleRunner::class) ? $services->get(EntitySaveLifecycleRunner::class) : null,
        $services->has(ErrorHandler::class) ? $services->get(ErrorHandler::class) : null,
        $services->get(PageRevisionService::class),
    ),
    PageSeoContributor::class => static fn (ServiceContainer $services): PageSeoContributor => new PageSeoContributor(),
    PageSitemapContributor::class => static fn (ServiceContainer $services): PageSitemapContributor => new PageSitemapContributor($services->get(\Zoosper\Page\Repository\PageRepository::class)),
    PageReferenceInspector::class => static fn (ServiceContainer $services): PageReferenceInspector => new PageReferenceInspector($services->get(PDO::class)),
    PageLifecycleCoordinator::class => static fn (ServiceContainer $services): PageLifecycleCoordinator => new PageLifecycleCoordinator(
        $services->get(PDO::class),
        $services->get(PageRepository::class),
        $services->get(PageRevisionService::class),
        $services->get(PageRevisionRepository::class),
        $services->get(PageReferenceInspector::class),
        $services->has(AuditLoggerInterface::class) ? $services->get(AuditLoggerInterface::class) : null,
    ),
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
        $services->has(SeoMetadataManager::class)
            ? $services->get(SeoMetadataManager::class)
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
    StarterSiteInstallCommand::class => static fn (ServiceContainer $services): StarterSiteInstallCommand => new StarterSiteInstallCommand(
        $services->get(SiteRepository::class),
        $services->get(\Zoosper\Page\Repository\PageRepository::class),
    ),

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

    \Zoosper\Page\Application\Publication\PagePublicationCoordinator::class => static fn (\Zoosper\Core\Container\ServiceContainer $services): \Zoosper\Page\Application\Publication\PagePublicationCoordinator => new \Zoosper\Page\Application\Publication\PagePublicationCoordinator(
        $services->get(\Zoosper\Page\Repository\PageRepository::class),
        $services->has(\Zoosper\Core\Event\EventDispatcherInterface::class) ? $services->get(\Zoosper\Core\Event\EventDispatcherInterface::class) : null,
    ),
];











