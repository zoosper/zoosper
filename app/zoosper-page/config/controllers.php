<?php

declare(strict_types=1);

use Zoosper\Admin\Editor\ContentEditorInterface;
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
use Zoosper\Page\Admin\Controller\PageAdminController;
use Zoosper\Page\Admin\PageGridDataSource;
use Zoosper\Page\Admin\PageGridDefinition;
use Zoosper\Page\Admin\PageGridRepository;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

return [
    // Phase 1.41 (partial, round 3a): layout/views now resolved via
    // AdminLayoutRendererInterface / AdminViewRendererInterface instead of
    // the concrete Zoosper\Admin\Layout\AdminLayout /
    // Zoosper\Admin\UI\AdminViewRenderer classes.
    PageAdminController::class => static fn (ServiceContainer $services): PageAdminController => new PageAdminController(
        guard: $services->get(SessionGuard::class),
        csrf: $services->get(CsrfTokenManager::class),
        pages: $services->get(PageRepository::class),
        sites: $services->get(SiteRepository::class),
        renderer: $services->get(PageRenderer::class),
        layout: $services->get(AdminLayoutRendererInterface::class),
        views: $services->get(AdminViewRendererInterface::class),
        pageGrid: $pageGrid = new PageGridRepository($services->get(\PDO::class)),
        pageGridDefinition: new PageGridDefinition(
            $services->has(GridColumnRegistry::class) ? $services->get(GridColumnRegistry::class) : null,
        ),
        pageGridDataSource: new PageGridDataSource($pageGrid),
        gridHtmlRenderer: new GridHtmlRenderer(),
        htmlSanitizer: $services->has(HtmlSanitizerInterface::class) ? $services->get(HtmlSanitizerInterface::class) : null,
        flashMessages: $services->has(FlashMessageStoreInterface::class) ? $services->get(FlashMessageStoreInterface::class) : null,
        config: $services->has(ConfigRepository::class) ? $services->get(ConfigRepository::class) : null,
        contentEditor: $services->has(ContentEditorInterface::class) ? $services->get(ContentEditorInterface::class) : null,
        translator: $services->has(TranslatorInterface::class) ? $services->get(TranslatorInterface::class) : null,
        adminContextTranslatorResolver: $services->has(AdminContextTranslatorResolver::class) ? $services->get(AdminContextTranslatorResolver::class) : null,
        saveLifecycle: $services->get(EntitySaveLifecycleRunner::class),
        errorHandler: $services->has(ErrorHandler::class) ? $services->get(ErrorHandler::class) : null,
        events: $services->has(EventDispatcherInterface::class) ? $services->get(EventDispatcherInterface::class) : null,
    ),
];

