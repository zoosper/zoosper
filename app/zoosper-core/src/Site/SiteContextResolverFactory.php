<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Creates the SiteContextResolver.
 *
 * Phase 1.34c: SiteRepository is injected when available so the DB-backed site
 * table becomes the primary source of truth. config/sites.php remains as a
 * bootstrap fallback when the DB/site module is not available or no host matches.
 */
final readonly class SiteContextResolverFactory
{
    public function __construct(
        private ConfigRepository $config,
        private ?SiteRepository $sites = null,
    ) {
    }

    public function create(): SiteContextResolver
    {
        return new SiteContextResolver($this->config->array('sites'), $this->sites);
    }
}
