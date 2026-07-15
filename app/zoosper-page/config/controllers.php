<?php

declare(strict_types=1);

/**
 * Page module admin controller registrations.
 *
 * Phase 1.27: AdminViewRenderer is required (the page index is rendered by a
 * Latte template), and ErrorHandler is injected so page save exceptions are
 * logged before the controller returns a 422.
 */

use Zoosper\Admin\Controller\PageAdminController;
use Zoosper\Admin\Editor\ContentEditorInterface;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Message\FlashMessageStoreInterface;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Core\I18n\AdminContextTranslatorResolver;
use Zoosper\Core\I18n\TranslatorInterface;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Page\Admin\PageGridRepository;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

return [
    PageAdminController::class => static fn (ServiceContainer $services): PageAdminController => new PageAdminController(
        guard: $services->get(SessionGuard::class),
        csrf: $services->get(CsrfTokenManager::class),
        pages: $services->get(PageRepository::class),
        sites: $services->get(SiteRepository::class),
        renderer: $services->get(PageRenderer::class),
        layout: $services->get(AdminLayout::class),
        views: $services->get(AdminViewRenderer::class),
        pageGrid: new PageGridRepository($services->get(\PDO::class)),
        htmlSanitizer: $services->has(HtmlSanitizerInterface::class) ? $services->get(HtmlSanitizerInterface::class) : null,
        flashMessages: $services->has(FlashMessageStoreInterface::class) ? $services->get(FlashMessageStoreInterface::class) : null,
        config: $services->has(ConfigRepository::class) ? $services->get(ConfigRepository::class) : null,
        contentEditor: $services->has(ContentEditorInterface::class) ? $services->get(ContentEditorInterface::class) : null,
        translator: $services->has(TranslatorInterface::class) ? $services->get(TranslatorInterface::class) : null,
        adminContextTranslatorResolver: $services->has(AdminContextTranslatorResolver::class) ? $services->get(AdminContextTranslatorResolver::class) : null,
        saveLifecycle: $services->get(EntitySaveLifecycleRunner::class),
        errorHandler: $services->has(ErrorHandler::class) ? $services->get(ErrorHandler::class) : null,
    ),
];
