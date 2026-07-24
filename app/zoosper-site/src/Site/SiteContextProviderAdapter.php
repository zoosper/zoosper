<?php

declare(strict_types=1);

namespace Zoosper\Site\Site;

use Zoosper\Core\Site\SiteContext;
use Zoosper\Core\Site\SiteContextProviderInterface;

/**
 * Site-module adapter for the core site context provider contract.
 *
 * This is intentionally a safe no-op proof adapter. A later phase can delegate
 * to the real site repository/resolver once wiring tests are in place.
 */
final class SiteContextProviderAdapter implements SiteContextProviderInterface
{
    public function resolve(object $request): ?SiteContext
    {
        return null;
    }
}
