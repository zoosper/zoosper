<?php

declare(strict_types=1);

use Zoosper\AdminForm\AdminFormRegistry;
use Zoosper\AdminForm\AdminFormRenderer;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Media\Api\MediaApiController;
use Zoosper\Media\Repository\MediaDerivativeRepository;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Media\Controller\MediaAdminController;
use Zoosper\Media\Admin\Grid\MediaVisualGridWorkspace;
use Zoosper\Media\Controller\MediaEditorJsUploadController;
use Zoosper\Media\EditorJs\EditorJsImageUploadResponseFactory;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Lifecycle\MediaLifecycleCoordinator;

return [
    MediaApiController::class => static fn (ServiceContainer $services): MediaApiController => new MediaApiController(
        json: $services->get(JsonResponder::class),
        auth: $services->get(PersonalAccessTokenAuthenticator::class),
        assets: $services->get(MediaAssetRepository::class),
        derivatives: $services->get(MediaDerivativeRepository::class),
        uploads: $services->get(MediaUploadService::class),
        lifecycle: $services->get(MediaLifecycleCoordinator::class),
        audit: $services->has(AuditLoggerInterface::class) ? $services->get(AuditLoggerInterface::class) : null,
    ),
    MediaAdminController::class => static fn (ServiceContainer $services): MediaAdminController => new MediaAdminController(
        guard: $services->get(SessionGuard::class),
        csrf: $services->get(CsrfTokenManager::class),
        views: $services->get(AdminViewRendererInterface::class),
        assets: $services->get(MediaAssetRepository::class),
        uploads: $services->get(MediaUploadService::class),
        formRegistry: $services->get(AdminFormRegistry::class),
        formRenderer: $services->get(AdminFormRenderer::class),
        adminUrls: $services->get(AdminUrlGenerator::class),
        lifecycle: $services->get(MediaLifecycleCoordinator::class),
        visualGrid: $services->get(MediaVisualGridWorkspace::class),
        flash: $services->has(FlashMessageStoreInterface::class) ? $services->get(FlashMessageStoreInterface::class) : null,
    ),
    MediaEditorJsUploadController::class => static fn (ServiceContainer $services): MediaEditorJsUploadController => new MediaEditorJsUploadController(
        guard: $services->get(SessionGuard::class),
        uploads: $services->get(MediaUploadService::class),
        responses: $services->get(EditorJsImageUploadResponseFactory::class),
    ),
];











