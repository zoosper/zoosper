<?php

declare(strict_types=1);

use Marko\View\ViewInterface;
use Zoosper\Theme\Template\MarkoViewAdapter;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Config\Scope\ScopeConfigRepository;
use Zoosper\Core\Config\Scope\ScopeContext;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\View\TemplateViewContextProvider;
use Zoosper\Theme\Config\TemplateRuntimeConfig;
use Zoosper\Theme\Layout\LayoutUpdateRepository;
use Zoosper\Theme\Template\Engine\LatteTemplateEngine;
use Zoosper\Theme\Template\Engine\PhpTemplateEngine;
use Zoosper\Theme\Template\Engine\TemplateEngineInterface;
use Zoosper\Theme\Template\Engine\TemplateEngineRegistry;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\ThemeRepository;
use Zoosper\Theme\Theme\ThemeResolver;
use Zoosper\Theme\Application\ThemeAssignmentService;
use Zoosper\Site\Repository\SiteRepository;

return [
    ThemeAssignmentService::class => static fn (ServiceContainer $s): ThemeAssignmentService => new ThemeAssignmentService($s->get(ThemeRepository::class),$s->get(SiteRepository::class)),
    ViewInterface::class => static fn (ServiceContainer $services): ViewInterface => new MarkoViewAdapter(
        $services->get('theme.frontend_template_renderer'),
    ),
    ThemeRepository::class => static fn (ServiceContainer $services): ThemeRepository => new ThemeRepository(dirname(__DIR__, 3) . '/themes'),
    LayoutUpdateRepository::class => static fn (ServiceContainer $services): LayoutUpdateRepository => new LayoutUpdateRepository(),
    ScopeConfigRepository::class => static fn (ServiceContainer $services): ScopeConfigRepository => new ScopeConfigRepository($services->get(PDO::class)),
    TemplateRuntimeConfig::class => static fn (ServiceContainer $services): TemplateRuntimeConfig => new TemplateRuntimeConfig(
        dirname(__DIR__, 3),
        $services->get(ConfigRepository::class),
        $services->get(ScopeConfigRepository::class),
        ScopeContext::default(),
    ),
    PhpTemplateEngine::class => static fn (ServiceContainer $services): PhpTemplateEngine => new PhpTemplateEngine(),
    LatteTemplateEngine::class => static fn (ServiceContainer $services): LatteTemplateEngine => new LatteTemplateEngine(
        $services->get(TemplateRuntimeConfig::class)->cacheDirectory(),
    ),
    TemplateEngineInterface::class => static fn (ServiceContainer $services): TemplateEngineInterface => $services->get(
        $services->get(TemplateRuntimeConfig::class)->engine() === 'php'
            ? PhpTemplateEngine::class
            : LatteTemplateEngine::class,
    ),
    TemplateEngineRegistry::class => static fn (ServiceContainer $services): TemplateEngineRegistry => (new TemplateEngineRegistry(
        $services->get(LatteTemplateEngine::class),
        $services->get(PhpTemplateEngine::class),
    ))->prioritise([$services->get(TemplateRuntimeConfig::class)->engine(), 'latte', 'php']),
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
