<?php

declare(strict_types=1);

use Zoosper\Core\App\CmsVersion;
use Zoosper\Core\Cache\CacheKeyBuilder;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
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

        return new HtmlSanitizerFactory($config);
    },
    HtmlSanitizerInterface::class => static fn (ServiceContainer $services): HtmlSanitizerInterface => $services->get(HtmlSanitizerFactory::class)->create(),
];
