<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\View\TemplateViewContextProvider;
use Zoosper\Theme\Layout\LayoutUpdateRepository;
use Zoosper\Theme\Template\Engine\LatteTemplateEngine;
use Zoosper\Theme\Template\Engine\PhpTemplateEngine;
use Zoosper\Theme\Template\Engine\TemplateEngineInterface;
use Zoosper\Theme\Template\Engine\TemplateEngineRegistry;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\ThemeRepository;
use Zoosper\Theme\Theme\ThemeResolver;

return [
    ThemeRepository::class => static fn (ServiceContainer $services): ThemeRepository => new ThemeRepository(dirname(__DIR__, 3) . '/themes'),
    LayoutUpdateRepository::class => static fn (ServiceContainer $services): LayoutUpdateRepository => new LayoutUpdateRepository(),
    PhpTemplateEngine::class => static fn (ServiceContainer $services): PhpTemplateEngine => new PhpTemplateEngine(),
    LatteTemplateEngine::class => static function (ServiceContainer $services): LatteTemplateEngine {
        $config = $services->get(ConfigRepository::class)->array('template');
        $relativeCachePath = (string) ($config['template_cache_path'] ?? 'var/cache/templates');
        $cachePath = dirname(__DIR__, 3) . '/' . ltrim($relativeCachePath, '/');

        return new LatteTemplateEngine($cachePath);
    },
    TemplateEngineInterface::class => static fn (ServiceContainer $services): TemplateEngineInterface => $services->get(LatteTemplateEngine::class),
    TemplateEngineRegistry::class => static fn (ServiceContainer $services): TemplateEngineRegistry => new TemplateEngineRegistry(
        $services->get(LatteTemplateEngine::class),
        $services->get(PhpTemplateEngine::class),
    ),
    'theme.frontend_template_renderer' => static fn (ServiceContainer $services): TemplateRenderer => new TemplateRenderer(
        new ThemeResolver(dirname(__DIR__, 3) . '/themes', 'default'),
        $services->get(ModuleRegistry::class),
        $services->get(LayoutUpdateRepository::class),
        $services->get(TemplateViewContextProvider::class),
        $services->get(TemplateEngineRegistry::class),
    ),
    'theme.admin_template_renderer' => static fn (ServiceContainer $services): TemplateRenderer => new TemplateRenderer(
        new ThemeResolver(dirname(__DIR__, 3) . '/themes/admin', 'default'),
        $services->get(ModuleRegistry::class),
        $services->get(LayoutUpdateRepository::class),
        $services->get(TemplateViewContextProvider::class),
        $services->get(TemplateEngineRegistry::class),
    ),
    TemplateRenderer::class => static fn (ServiceContainer $services): TemplateRenderer => $services->get('theme.frontend_template_renderer'),
];
