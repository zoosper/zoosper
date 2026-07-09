<?php

declare(strict_types=1);

namespace Zoosper\Site\Context;

use Zoosper\Site\Model\Site;

final readonly class SiteContext
{
    public function __construct(
        public Site $site,
        public string $host,
    ) {
    }
}
