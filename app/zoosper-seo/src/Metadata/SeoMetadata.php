<?php
declare(strict_types=1);
namespace Zoosper\Seo\Metadata;
final readonly class SeoMetadata{public function __construct(public string $title,public ?string $description=null,public ?string $canonicalUrl=null,public string $robots='noindex,nofollow',public ?string $openGraphTitle=null,public ?string $openGraphDescription=null,public ?string $openGraphUrl=null){}public function toLayoutData():array{return ['title'=>$this->title,'metaDescription'=>$this->description,'canonicalUrl'=>$this->canonicalUrl,'robots'=>$this->robots,'openGraphTitle'=>$this->openGraphTitle??$this->title,'openGraphDescription'=>$this->openGraphDescription??$this->description,'openGraphUrl'=>$this->openGraphUrl??$this->canonicalUrl];}}
