<?php

declare(strict_types=1);

use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Editor\ContentEditorInterface;
use Zoosper\Editor\Config\ContentEditorRuntimeConfig;
use Zoosper\Editor\Config\ContentEditorRuntimeConfigFactory;
use Zoosper\Editor\ContentEditorRegistry;
use Zoosper\Editor\EditorJsContentEditor;
use Zoosper\Editor\TextareaContentEditor;
use Zoosper\Media\EditorJs\EditorJsImageToolConfig;
use Zoosper\ScopedConfig\ScopeConfigRepository;

return [
    ContentEditorRuntimeConfigFactory::class => static fn (ServiceContainer $services): ContentEditorRuntimeConfigFactory => new ContentEditorRuntimeConfigFactory(
        $services->get(ConfigRepository::class),
        $services->get(ScopeConfigRepository::class),
    ),
    ContentEditorRuntimeConfig::class => static fn (ServiceContainer $services): ContentEditorRuntimeConfig => $services
        ->get(ContentEditorRuntimeConfigFactory::class)
        ->forDefaultScope(),
    TextareaContentEditor::class => static fn(ServiceContainer $services): TextareaContentEditor => new TextareaContentEditor(),
    EditorJsContentEditor::class => static fn(ServiceContainer $services): EditorJsContentEditor => new EditorJsContentEditor(
        $services->get(TextareaContentEditor::class),
        $services->has(EditorJsImageToolConfig::class) ? $services->get(EditorJsImageToolConfig::class) : null,
        $services->get(CsrfTokenManager::class),
    ),
    ContentEditorRegistry::class => static fn(ServiceContainer $services): ContentEditorRegistry => new ContentEditorRegistry(
        $services->get(EditorJsContentEditor::class),
        $services->get(TextareaContentEditor::class),
    ),
    ContentEditorInterface::class => static fn (ServiceContainer $services): ContentEditorInterface => $services
        ->get(ContentEditorRegistry::class)
        ->preferred(
            $services->get(ContentEditorRuntimeConfig::class)->preferred(),
            $services->get(ContentEditorRuntimeConfig::class)->fallback(),
        ),
];
