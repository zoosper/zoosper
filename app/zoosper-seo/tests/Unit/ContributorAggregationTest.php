<?php
declare(strict_types=1);
use Zoosper\Seo\Contract\SitemapContributorInterface;use Zoosper\Seo\Sitemap\{SitemapAggregator,SitemapEntry};use Zoosper\Site\Model\Site;
it('aggregates multiple contributors deterministically and deduplicates',function(){ $a=new class implements SitemapContributorInterface{public function entries(Site $s):iterable{yield new SitemapEntry('https://example.test/b');yield new SitemapEntry('https://example.test/a');}};$b=new class implements SitemapContributorInterface{public function entries(Site $s):iterable{yield new SitemapEntry('https://example.test/a');yield new SitemapEntry('javascript:bad');}};$xml=(new SitemapAggregator([$a,$b]))->xml(new Site(1,'main','Main','active'));expect(substr_count($xml,'https://example.test/a'))->toBe(1)->and(strpos($xml,'/a'))->toBeLessThan(strpos($xml,'/b'))->and($xml)->not->toContain('javascript:');});










