<?php

declare(strict_types=1);

use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Media\Controller\MediaAdminController;
use Zoosper\Media\Controller\MediaEditorJsUploadController;
use Zoosper\Media\EditorJs\EditorJsImageUploadResponseFactory;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaUploadService;

return [
    MediaAdminController::class => static fn (ServiceContainer $services): MediaAdminController => new MediaAdminController(
        guard: $services->get(SessionGuard::class),
        csrf: $services->get(CsrfTokenManager::class),
        views: $services->get(AdminViewRendererInterface::class),
        assets: $services->get(MediaAssetRepository::class),
        uploads: $services->get(MediaUploadService::class),
        adminUrls: $services->get(AdminUrlGenerator::class),
    ),
    MediaEditorJsUploadController::class => static fn (ServiceContainer $services): MediaEditorJsUploadController => new MediaEditorJsUploadController(
        guard: $services->get(SessionGuard::class),
        uploads: $services->get(MediaUploadService::class),
        responses: $services->get(EditorJsImageUploadResponseFactory::class),
    ),
];
