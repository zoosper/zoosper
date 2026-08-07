<?php

declare(strict_types=1);

use Zoosper\Admin\Editor\ContentEditorInterface;
use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridWorkspaceAuditedCsvExportService;
use Zoosper\Page\Admin\Controller\PageCsvExportController;
use Zoosper\Page\Admin\PageGridAuditedExportCoordinator;
use Zoosper\Page\Admin\PageGridExportDataSource;
use Zoosper\Page\Admin\PageGridExportRequestCoordinator;
use Zoosper\Page\Admin\PageGridExportSqlBuilder;
use Zoosper\Page\Admin\PageGridHttpCoordinator;
use Zoosper\Page\Admin\PageGridMutationHandler;
use Zoosper\Page\Admin\PdoPageGridExportRepository;
use Zoosper\AdminGrid\GridWorkspaceMutationGuard;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\AdminGrid\GridBulkActionManifestRenderer;
use Zoosper\Page\Admin\PageGridMutationCoordinator;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Page\Admin\PageGridSiteFilter;
use Zoosper\Page\Admin\PageGridWorkspace;
use Zoosper\Page\Admin\Save\PageSaveCoordinator;
use Zoosper\Page\Admin\Publication\PagePublicationCoordinator;
use Zoosper\Core\Form\AdminFormProcessorConfigFactory;
use Zoosper\Page\Admin\PageSiteFilterOptions;

use Zoosper\Admin\Message\FlashMessageStoreInterface;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Event\EventDispatcherInterface;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridHtmlRenderer;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Core\I18n\AdminContextTranslatorResolver;
use Zoosper\Core\I18n\TranslatorInterface;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Page\Admin\Controller\PageBulkActionController;
use Zoosper\Page\Admin\BulkAction\PageBulkActionBackend;
use Zoosper\AdminGrid\BulkAction\GridBulkHostBindings;
use Zoosper\AdminGrid\BulkAction\GridBulkExecutionResultAdapter;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Page\Admin\Controller\PageAdminController;
use Zoosper\Page\Admin\PageGridDataSource;
use Zoosper\Page\Admin\PageGridDefinition;
use Zoosper\Page\Admin\PageGridRepository;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

return [
    PageCsvExportController::class => static function (ServiceContainer $services): PageCsvExportController {
        $definition = new PageGridDefinition(
            $services->has(GridColumnRegistry::class) ? $services->get(GridColumnRegistry::class) : null,
            new PageGridSiteFilter(new PageSiteFilterOptions($services->get(SiteRepository::class))),
            $services->get(AdminUrlGenerator::class),
        );
        $workspace = new PageGridWorkspace(
            $definition,
            $services->get(GridViewStateResolver::class),
            new GridCompactWorkspaceRenderer(),
            adminUrls: $services->get(AdminUrlGenerator::class),
        );
        $http = new PageGridHttpCoordinator(
            $workspace,
            new PageGridMutationHandler($definition, $services->get(\Zoosper\AdminGrid\GridViewMutationService::class)),
            $services->get(GridWorkspaceMutationGuard::class),
        );
        $repository = new PdoPageGridExportRepository($services->get(\PDO::class), new PageGridExportSqlBuilder());
        $requestExports = new PageGridExportRequestCoordinator(
            $http,
            new PageGridExportDataSource($repository),
            new PageGridAuditedExportCoordinator($services->get(GridWorkspaceAuditedCsvExportService::class)),
        );
        return new PageCsvExportController(
            $services->get(SessionGuard::class),
            $requestExports,
            $services->get(AdminUrlGenerator::class),
        );
    },    // Phase 1.41 (partial, round 3a): layout/views now resolved via
    // AdminLayoutRendererInterface / AdminViewRendererInterface instead of
    // the concrete Zoosper\Admin\Layout\AdminLayout /
    // Zoosper\Admin\UI\AdminViewRenderer classes.
    PageBulkActionController::class => static fn (ServiceContainer $services): PageBulkActionController => new PageBulkActionController(
        guard: $services->get(SessionGuard::class),
        csrf: $services->get(CsrfTokenManager::class),
        pages: $services->get(PageRepository::class),
        events: $services->get(EventDispatcherInterface::class),
        audit: $services->get(AuditLoggerInterface::class),
        flashMessages: $services->get(FlashMessageStoreInterface::class),
        resultAdapter: new GridBulkExecutionResultAdapter(),
        adminUrls: $services->get(AdminUrlGenerator::class),
    ),
    PageAdminController::class => static fn (ServiceContainer $services): PageAdminController => new PageAdminController(
        guard: $services->get(SessionGuard::class),
        csrf: $services->get(CsrfTokenManager::class),
        pages: $services->get(PageRepository::class),
        sites: $services->get(SiteRepository::class),
        renderer: $services->get(PageRenderer::class),
        layout: $services->get(AdminLayoutRendererInterface::class),
        views: $services->get(AdminViewRendererInterface::class),
        pageGrid: $pageGrid = new PageGridRepository($services->get(\PDO::class)),
        pageGridDefinition: $pageGridDefinition = new PageGridDefinition(
            $services->has(GridColumnRegistry::class) ? $services->get(GridColumnRegistry::class) : null,
            new PageGridSiteFilter(new PageSiteFilterOptions($services->get(SiteRepository::class))),
            $services->get(AdminUrlGenerator::class),
        ),
        pageGridDataSource: new PageGridDataSource($pageGrid),
        gridHtmlRenderer: new GridHtmlRenderer(),
        gridMutationForms: $services->get(GridWorkspaceMutationFormsRenderer::class),
        gridBulkManifest: new GridBulkActionManifestRenderer(),
        pageGridMutations: new PageGridMutationCoordinator(
            new PageGridMutationHandler(
                $pageGridDefinition,
                $services->get(\Zoosper\AdminGrid\GridViewMutationService::class),
            ),
            $services->get(GridWorkspaceMutationGuard::class),
        ),
        pageGridWorkspace: $services->has(GridViewStateResolver::class)
            ? new PageGridWorkspace(
                $pageGridDefinition,
                $services->get(GridViewStateResolver::class),
                new GridCompactWorkspaceRenderer(),
                adminUrls: $services->get(AdminUrlGenerator::class),
            )
            : null,
        flashMessages: $services->has(FlashMessageStoreInterface::class) ? $services->get(FlashMessageStoreInterface::class) : null,
        config: $services->has(ConfigRepository::class) ? $services->get(ConfigRepository::class) : null,
        contentEditor: $services->has(ContentEditorInterface::class) ? $services->get(ContentEditorInterface::class) : null,
        translator: $services->has(TranslatorInterface::class) ? $services->get(TranslatorInterface::class) : null,
        adminContextTranslatorResolver: $services->has(AdminContextTranslatorResolver::class) ? $services->get(AdminContextTranslatorResolver::class) : null,
        pageSaver: new PageSaveCoordinator(
            $services->get(PageRepository::class),
            $services->has(HtmlSanitizerInterface::class) ? $services->get(HtmlSanitizerInterface::class) : null,
            $services->has(ConfigRepository::class) ? $services->get(ConfigRepository::class) : null,
            $services->has(AdminFormProcessorConfigFactory::class)
                ? $services->get(AdminFormProcessorConfigFactory::class)->create(
                    $services->has(ConfigRepository::class) ? $services->get(ConfigRepository::class)->array('admin_forms') : [],
                )
                : null,
            $services->get(EntitySaveLifecycleRunner::class),
            $services->has(ErrorHandler::class) ? $services->get(ErrorHandler::class) : null,
        ),
        publication: new PagePublicationCoordinator(
            $services->get(PageRepository::class),
            $services->has(EventDispatcherInterface::class) ? $services->get(EventDispatcherInterface::class) : null,
        ),
        adminUrls: $services->get(AdminUrlGenerator::class),
    ),
];

