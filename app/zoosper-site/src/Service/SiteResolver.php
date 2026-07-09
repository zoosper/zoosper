<?php

declare(strict_types=1);

namespace Zoosper\Site\Service;

use Zoosper\Site\Context\SiteContext;
use Zoosper\Site\Repository\SiteRepository;

final readonly class SiteResolver
{
    public function __construct(private SiteRepository $sites)
    {
    }

    public function resolve(string $host): ?SiteContext
    {
        $site = $this->sites->findActiveByHost($host);

        if ($site === null) {
            return null;
        }

        return new SiteContext($site, $host);
    }
}
