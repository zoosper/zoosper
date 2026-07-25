<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

/**
 * Safe no-op Site lookup used when no Site module adapter is available.
 */
final class NullSiteLookup implements SiteLookupInterface
{
    public function findByHost(string $host): ?ResolvedSite
    {
        return null;
    }

    public function findActiveByHost(string $host): ?ResolvedSite
    {
        return null;
    }

    public function findByCode(string $code): ?ResolvedSite
    {
        return null;
    }

    public function findDefault(): ?ResolvedSite
    {
        return null;
    }
}
