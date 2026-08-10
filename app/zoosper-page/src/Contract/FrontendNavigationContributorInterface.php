<?php

declare(strict_types=1);

namespace Zoosper\Page\Contract;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContext;

/** Optional navigation contribution consumed by PageRenderer without knowing Menu internals. */
interface FrontendNavigationContributorInterface
{
    /** @return array{navigationHtml:string,breadcrumbsHtml:string} */
    public function contribute(SiteContext $siteContext, Request $request): array;
}
