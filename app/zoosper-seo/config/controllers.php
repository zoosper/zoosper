<?php
declare(strict_types=1);
use Zoosper\Core\Container\ServiceContainer;use Zoosper\Seo\Controller\SeoPublicController;use Zoosper\Seo\Sitemap\SitemapAggregator;use Zoosper\Site\Repository\SiteRepository;
return [SeoPublicController::class=>static fn(ServiceContainer $s):SeoPublicController=>new SeoPublicController($s->get(SiteRepository::class),$s->get(SitemapAggregator::class))];










