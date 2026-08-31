<?php
declare(strict_types=1);
use Zoosper\Core\Container\ServiceContainer;use Zoosper\Core\Module\ModuleRegistry;use Zoosper\Seo\Contribution\SeoContributorRegistry;use Zoosper\Seo\Metadata\SeoMetadataManager;use Zoosper\Seo\Sitemap\SitemapAggregator;
return [SeoContributorRegistry::class=>static fn(ServiceContainer $s):SeoContributorRegistry=>new SeoContributorRegistry($s->get(ModuleRegistry::class),$s),SeoMetadataManager::class=>static fn(ServiceContainer $s):SeoMetadataManager=>new SeoMetadataManager($s->get(SeoContributorRegistry::class)->metadata()),SitemapAggregator::class=>static fn(ServiceContainer $s):SitemapAggregator=>new SitemapAggregator($s->get(SeoContributorRegistry::class)->sitemap())];










