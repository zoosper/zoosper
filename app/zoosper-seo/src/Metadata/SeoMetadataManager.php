<?php
declare(strict_types=1);
namespace Zoosper\Seo\Metadata;
use Zoosper\Core\Http\Request;use Zoosper\Seo\Contract\SeoMetadataContributorInterface;use Zoosper\Site\Model\Site;
final readonly class SeoMetadataManager{/** @param list<SeoMetadataContributorInterface> $contributors */public function __construct(private array $contributors){}public function for(object $resource,Site $site,?Request $request=null):?SeoMetadata{foreach($this->contributors as $contributor){$metadata=$contributor->contribute($resource,$site,$request);if($metadata!==null)return $metadata;}return null;}}
