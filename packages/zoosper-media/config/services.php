<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer;
use Zoosper\Media\EditorJs\EditorJsImageToolConfig;
use Zoosper\Media\EditorJs\EditorJsImageUploadResponseFactory;
use Zoosper\Media\Processing\MediaProcessingPolicy;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaStoredFileCleanupService;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Service\MediaUploadValidator;

return [
    MediaAssetRepository::class => static fn (ServiceContainer $services): MediaAssetRepository => new MediaAssetRepository($services->get(PDO::class)),
    MediaUploadValidator::class => static fn (ServiceContainer $services): MediaUploadValidator => new MediaUploadValidator(),
    MediaStorage::class => static fn (ServiceContainer $services): MediaStorage => new MediaStorage(dirname(__DIR__, 3)),
    MediaStoredFileCleanupService::class => static fn (ServiceContainer $services): MediaStoredFileCleanupService => new MediaStoredFileCleanupService(dirname(__DIR__, 3)),
    MediaUploadService::class => static fn (ServiceContainer $services): MediaUploadService => new MediaUploadService(
        assets: $services->get(MediaAssetRepository::class),
        validator: $services->get(MediaUploadValidator::class),
        storage: $services->get(MediaStorage::class),
        basePath: dirname(__DIR__, 3),
        errorHandler: $services->has(ErrorHandler::class) ? $services->get(ErrorHandler::class) : null,
        cleanup: $services->get(MediaStoredFileCleanupService::class),
    ),
    EditorJsImageUploadResponseFactory::class => static fn (ServiceContainer $services): EditorJsImageUploadResponseFactory => new EditorJsImageUploadResponseFactory(),
    EditorJsImageToolConfig::class => static fn (ServiceContainer $services): EditorJsImageToolConfig => new EditorJsImageToolConfig(),
    EditorJsImageBlockSanitizer::class => static fn (ServiceContainer $services): EditorJsImageBlockSanitizer => new EditorJsImageBlockSanitizer(),
    MediaProcessingPolicy::class => static fn (ServiceContainer $services): MediaProcessingPolicy => new MediaProcessingPolicy(),
];
