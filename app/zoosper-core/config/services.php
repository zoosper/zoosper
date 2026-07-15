<?php

declare(strict_types=1);

use Zoosper\Core\App\CmsVersion;
use Zoosper\Core\Cache\CacheKeyBuilder;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Entity\Extension\EntityExtensionDataPersister;
use Zoosper\Core\Entity\Extension\EntityExtensionValueRepository;
use Zoosper\Core\Filesystem\ProjectPathResolver;
use Zoosper\Core\Html\HtmlSanitizerFactory;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Site\CurrentSiteContext;
use Zoosper\Core\Site\SiteContextResolver;
use Zoosper\Core\Site\SiteContextResolverFactory;
use Zoosper\Core\Url\CdnUrlResolver;
use Zoosper\Core\Url\CdnUrlResolverFactory;
use Zoosper\Core\View\TemplateViewContextProvider;

return [
    ProjectPathResolver::class => static fn (ServiceContainer $services): ProjectPathResolver => ProjectPathResolver::fromCoreModule(),
    JsonResponder::class => static fn (ServiceContainer $services): JsonResponder => new JsonResponder(),
    CmsVersion::class => static fn (ServiceContainer $services): CmsVersion => new CmsVersion($services->get(ConfigRepository::class)),
    SiteContextResolver::class => static fn (ServiceContainer $services): SiteContextResolver => (new SiteContextResolverFactory($services->get(ConfigRepository::class)))->create(),
    CurrentSiteContext::class => static fn (ServiceContainer $services): CurrentSiteContext => new CurrentSiteContext($services->get(SiteContextResolver::class)),
    CdnUrlResolver::class => static fn (ServiceContainer $services): CdnUrlResolver => (new CdnUrlResolverFactory($services->get(ConfigRepository::class)))->create(),
    CacheKeyBuilder::class => static fn (ServiceContainer $services): CacheKeyBuilder => new CacheKeyBuilder(),
    TemplateViewContextProvider::class => static fn (ServiceContainer $services): TemplateViewContextProvider => new TemplateViewContextProvider(
        $services->get(CurrentSiteContext::class),
        $services->get(CdnUrlResolver::class),
        $services->get(CacheKeyBuilder::class),
    ),
    HtmlSanitizerFactory::class => static function (ServiceContainer $services): HtmlSanitizerFactory {
        /** @var array<string, mixed> $config */
        $config = $services->get(ConfigRepository::class)->array('html_sanitizer');
        $paths = $services->get(ProjectPathResolver::class);
        $cachePath = (string) ($config['cache_path'] ?? 'var/cache/htmlpurifier');
        $config['cache_path'] = str_starts_with($cachePath, '/')
            ? $cachePath
            : $paths->varPath(str_starts_with($cachePath, 'var/') ? substr($cachePath, 4) : $cachePath);

        return new HtmlSanitizerFactory($config);
    },
    HtmlSanitizerInterface::class => static fn (ServiceContainer $services): HtmlSanitizerInterface => $services->get(HtmlSanitizerFactory::class)->create(),

    // Phase 1.29 follow-up: generic per-module entity extension value store.
    EntityExtensionValueRepository::class => static fn (ServiceContainer $services): EntityExtensionValueRepository => new EntityExtensionValueRepository($services->get(PDO::class)),
    EntityExtensionDataPersister::class => static fn (ServiceContainer $services): EntityExtensionDataPersister => new EntityExtensionDataPersister($services->get(EntityExtensionValueRepository::class)),
];
