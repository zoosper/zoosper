<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

/**
 * Safe default site context provider used before zoosper-site binds a concrete implementation.
 */
final class NullSiteContextProvider implements SiteContextProviderInterface
{
    public function resolve(object $request): ?SiteContext
    {
        return null;
    }
}
