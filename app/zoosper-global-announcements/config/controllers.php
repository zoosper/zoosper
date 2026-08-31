<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncementRepository;
use Zoosper\GlobalAnnouncements\Controller\AnnouncementAdminController;

return [
    AnnouncementAdminController::class => static fn (ServiceContainer $services): AnnouncementAdminController => new AnnouncementAdminController(
        guard: $services->get(SessionGuard::class),
        announcements: $services->get(AdminAnnouncementRepository::class),
        csrf: $services->get(CsrfTokenManager::class),
        layout: $services->get(AdminLayout::class),
        urls: $services->get(AdminUrlGenerator::class),
        flash: $services->has(FlashMessageStoreInterface::class) ? $services->get(FlashMessageStoreInterface::class) : null,
        views: $services->has(AdminViewRenderer::class) ? $services->get(AdminViewRenderer::class) : null,
        audit: $services->has(AuditLoggerInterface::class) ? $services->get(AuditLoggerInterface::class) : null,
    ),
];










