<?php
declare(strict_types=1);
namespace Zoosper\Seo\Contract;
use Zoosper\Core\Http\Request;use Zoosper\Seo\Metadata\SeoMetadata;use Zoosper\Site\Model\Site;
interface SeoMetadataContributorInterface{public function contribute(object $resource,Site $site,?Request $request=null):?SeoMetadata;}










