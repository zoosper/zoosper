<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

/**
 * Core-owned seam for resolving site context without importing site-module
 * repositories/models directly inside core bootstrap code.
 */
interface SiteContextProviderInterface
{
    public function resolve(object $request): ?SiteContext;
}
