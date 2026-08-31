<?php
declare(strict_types=1);
namespace Zoosper\Page\Seo;
use Zoosper\Page\Repository\PageRepository;use Zoosper\Seo\Contract\SitemapContributorInterface;use Zoosper\Seo\Sitemap\SitemapEntry;use Zoosper\Site\Model\Site;
final readonly class PageSitemapContributor implements SitemapContributorInterface{public function __construct(private PageRepository $pages){}public function entries(Site $s):iterable{foreach($this->pages->allPublishedForSite($s->id) as $p){$u=trim((string)$p->canonicalUrl);if($u===''){ $b=rtrim(trim($s->baseUrl),'/');if($b==='')continue;$u=$b.($p->slug===($s->homepageSlug??'home')?'/':'/'.ltrim($p->slug,'/'));}yield new SitemapEntry($u);}}}










