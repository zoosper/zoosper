<?php
declare(strict_types=1);
namespace Zoosper\Page\Seo;
use Zoosper\Core\Http\Request;use Zoosper\Page\Model\Page;use Zoosper\Seo\Contract\SeoMetadataContributorInterface;use Zoosper\Seo\Metadata\SeoMetadata;use Zoosper\Site\Model\Site;
final readonly class PageSeoContributor implements SeoMetadataContributorInterface{public function contribute(object $resource,Site $site,?Request $request=null):?SeoMetadata{if(!$resource instanceof Page)return null;$title=trim((string)$resource->metaTitle)?:$resource->title;$d=trim((string)$resource->metaDescription)?:null;$c=$this->canonical($resource,$site,$request);$robots=$resource->isPublished()&&$request!==null?'index,follow':'noindex,nofollow';return new SeoMetadata($title,$d,$c,$robots,$title,$d,$c);}private function canonical(Page $p,Site $s,?Request $r):?string{$explicit=trim((string)$p->canonicalUrl);if($explicit!=='')return $this->valid($explicit)?$explicit:null;$b=rtrim(trim($s->baseUrl),'/');if($r===null||!$p->isPublished()||!$this->valid($b))return null;return $b.($p->slug===($s->homepageSlug??'home')?'/':'/'.ltrim($p->slug,'/'));}private function valid(string $u):bool{$x=parse_url($u);return is_array($x)&&isset($x['host'])&&in_array(strtolower((string)($x['scheme']??'')),['http','https'],true);}}










