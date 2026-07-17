<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Media\Controller\MediaAdminController;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaUploadValidator;

return [
    MediaAdminController::class => static fn (ServiceContainer $services): MediaAdminController => new MediaAdminController(
        guard: $services->get(SessionGuard::class),
        csrf: $services->get(CsrfTokenManager::class),
        layout: $services->get(AdminLayout::class),
        views: $services->get(AdminViewRenderer::class),
        assets: $services->get(MediaAssetRepository::class),
        validator: $services->get(MediaUploadValidator::class),
        storage: $services->get(MediaStorage::class),
        errorHandler: $services->has(ErrorHandler::class) ? $services->get(ErrorHandler::class) : null,
    ),
];
