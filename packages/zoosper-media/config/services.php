<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\AdminGrid\{GridCompactWorkspaceRenderer,GridViewStateResolver};
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Media\Admin\Grid\{MediaGridSource,MediaVisualGridRenderer,MediaVisualGridWorkspace};
use Zoosper\Core\Error\ErrorHandler;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer;
use Zoosper\Media\EditorJs\EditorJsImageToolConfig;
use Zoosper\Media\EditorJs\EditorJsImageUploadResponseFactory;
use Zoosper\Media\Processing\MediaProcessingPolicy;
use Zoosper\Media\Processing\MediaProcessorInterface;
use Zoosper\Media\Processing\GdMediaProcessor;
use Zoosper\Media\Processing\QueuedMediaProcessor;
use Zoosper\Media\Console\ProcessMediaQueueCommand;
use Zoosper\Media\Processing\MediaUploadDerivativeDispatcher;
use Zoosper\Media\Processing\MediaUploadDerivativePolicy;
use Zoosper\Media\Lifecycle\MediaLifecycleCoordinator;
use Zoosper\Media\Lifecycle\MediaReferenceInspector;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Repository\MediaDerivativeRepository;
use Zoosper\Media\Service\MediaDerivativeLookup;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaCanonicalizerInterface;
use Zoosper\Media\Service\GdMediaCanonicalizer;
use Zoosper\Media\Service\MediaStoredFileCleanupService;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Service\MediaUploadValidator;

return [
    MediaGridSource::class => static fn (ServiceContainer $services): MediaGridSource => new MediaGridSource($services->get(PDO::class)),
    MediaVisualGridRenderer::class => static fn (ServiceContainer $services): MediaVisualGridRenderer => new MediaVisualGridRenderer($services->get(AdminUrlGenerator::class)),
    MediaVisualGridWorkspace::class => static fn (ServiceContainer $services): MediaVisualGridWorkspace => new MediaVisualGridWorkspace($services->get(GridViewStateResolver::class), new GridCompactWorkspaceRenderer(), new GridColumnOrderer(), $services->get(MediaGridSource::class), $services->get(MediaVisualGridRenderer::class)),
    MediaReferenceInspector::class => static fn (ServiceContainer $services): MediaReferenceInspector => new MediaReferenceInspector($services->get(PDO::class)),
    MediaAssetRepository::class => static fn (ServiceContainer $services): MediaAssetRepository => new MediaAssetRepository($services->get(PDO::class)),
    MediaDerivativeRepository::class => static fn (ServiceContainer $services): MediaDerivativeRepository => new MediaDerivativeRepository($services->get(PDO::class), dirname(__DIR__, 3)),
    MediaDerivativeLookup::class => static fn (ServiceContainer $services): MediaDerivativeLookup => new MediaDerivativeLookup($services->get(MediaDerivativeRepository::class)),
    MediaUploadValidator::class => static fn (ServiceContainer $services): MediaUploadValidator => new MediaUploadValidator(),
    MediaCanonicalizerInterface::class => static fn (ServiceContainer $services): MediaCanonicalizerInterface => new GdMediaCanonicalizer(),
    MediaStorage::class => static fn (ServiceContainer $services): MediaStorage => new MediaStorage(dirname(__DIR__, 3), $services->get(MediaCanonicalizerInterface::class)),
    MediaStoredFileCleanupService::class => static fn (ServiceContainer $services): MediaStoredFileCleanupService => new MediaStoredFileCleanupService(dirname(__DIR__, 3)),
    MediaUploadService::class => static fn (ServiceContainer $services): MediaUploadService => new MediaUploadService(
        assets: $services->get(MediaAssetRepository::class),
        validator: $services->get(MediaUploadValidator::class),
        storage: $services->get(MediaStorage::class),
        basePath: dirname(__DIR__, 3),
        errorHandler: $services->has(ErrorHandler::class) ? $services->get(ErrorHandler::class) : null,
        cleanup: $services->get(MediaStoredFileCleanupService::class),
        derivatives: $services->get(MediaUploadDerivativeDispatcher::class),
    ),
    EditorJsImageUploadResponseFactory::class => static fn (ServiceContainer $services): EditorJsImageUploadResponseFactory => new EditorJsImageUploadResponseFactory(),
    EditorJsImageToolConfig::class => static fn (ServiceContainer $services): EditorJsImageToolConfig => new EditorJsImageToolConfig(
        $services->get(AdminUrlGenerator::class)->url('media/editorjs/upload'),
    ),
    EditorJsImageBlockSanitizer::class => static fn (ServiceContainer $services): EditorJsImageBlockSanitizer => new EditorJsImageBlockSanitizer(),
    MediaProcessingPolicy::class => static fn (ServiceContainer $services): MediaProcessingPolicy => new MediaProcessingPolicy(),
    GdMediaProcessor::class => static fn (ServiceContainer $services): GdMediaProcessor => new GdMediaProcessor(
        dirname(__DIR__, 3),
        $services->get(MediaProcessingPolicy::class),
    ),
    MediaProcessorInterface::class => static fn (ServiceContainer $services): MediaProcessorInterface => new QueuedMediaProcessor(
        $services->get(PDO::class)
    ),
    MediaUploadDerivativePolicy::class => static fn (ServiceContainer $services): MediaUploadDerivativePolicy => new MediaUploadDerivativePolicy(true),
    MediaUploadDerivativeDispatcher::class => static fn (ServiceContainer $services): MediaUploadDerivativeDispatcher => new MediaUploadDerivativeDispatcher(
        $services->get(MediaProcessorInterface::class),
        $services->get(MediaUploadDerivativePolicy::class),
        $services->get(MediaProcessingPolicy::class)->defaultPlan(),
        $services->get(MediaDerivativeRepository::class),
    ),
    MediaLifecycleCoordinator::class => static fn (ServiceContainer $services): MediaLifecycleCoordinator => new MediaLifecycleCoordinator(
        $services->get(PDO::class),
        $services->get(MediaAssetRepository::class),
        cleanup: $services->get(MediaStoredFileCleanupService::class),
        derivatives: $services->get(MediaDerivativeRepository::class),
        references: $services->get(MediaReferenceInspector::class),
        audit: $services->has(AuditLoggerInterface::class) ? $services->get(AuditLoggerInterface::class) : null,
    ),
    ProcessMediaQueueCommand::class => static fn (ServiceContainer $services): ProcessMediaQueueCommand => new ProcessMediaQueueCommand(
        $services->get(PDO::class),
        $services->get(MediaAssetRepository::class),
        $services->get(GdMediaProcessor::class),
        $services->get(MediaDerivativeRepository::class)
    ),
];











