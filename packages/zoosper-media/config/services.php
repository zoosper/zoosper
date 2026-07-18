<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Media\EditorJs\EditorJsImageToolConfig;
use Zoosper\Media\EditorJs\EditorJsImageUploadResponseFactory;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaUploadValidator;

return [
    MediaAssetRepository::class => static fn (ServiceContainer $services): MediaAssetRepository => new MediaAssetRepository($services->get(PDO::class)),
    MediaUploadValidator::class => static fn (ServiceContainer $services): MediaUploadValidator => new MediaUploadValidator(),
    MediaStorage::class => static fn (ServiceContainer $services): MediaStorage => new MediaStorage(dirname(__DIR__, 3)),
    EditorJsImageUploadResponseFactory::class => static fn (ServiceContainer $services): EditorJsImageUploadResponseFactory => new EditorJsImageUploadResponseFactory(),
    EditorJsImageToolConfig::class => static fn (ServiceContainer $services): EditorJsImageToolConfig => new EditorJsImageToolConfig(),
];
