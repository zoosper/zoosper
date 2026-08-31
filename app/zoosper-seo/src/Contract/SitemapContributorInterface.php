<?php
declare(strict_types=1);
namespace Zoosper\Seo\Contract;
use Zoosper\Seo\Sitemap\SitemapEntry;use Zoosper\Site\Model\Site;
interface SitemapContributorInterface{/** @return iterable<SitemapEntry> */public function entries(Site $site):iterable;}










