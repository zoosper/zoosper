<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

/**
 * Core-owned lookup contract for resolving site data without importing the
 * Site module's repository or model classes.
 */
interface SiteLookupInterface
{
    public function findByHost(string $host): ?ResolvedSite;

    /**
     * Compatibility lookup for the historical active-site resolver path.
     */
    public function findActiveByHost(string $host): ?ResolvedSite;

    public function findByCode(string $code): ?ResolvedSite;

    public function findDefault(): ?ResolvedSite;
}










