<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Factory for configured report-only rate-limit middleware.
 */
final class RateLimitReportOnlyMiddlewareFactory
{
    public function __construct(
        private RateLimitStoreInterface $store,
        private RateLimitReportSinkInterface $reports,
    ) {
    }

    public function create(RateLimitRuntimeConfig $config): ReportOnlyRateLimitMiddleware
    {
        return new ReportOnlyRateLimitMiddleware(
            new RateLimitGuard(
                new StaticRateLimitPolicyResolver($config->policies),
                new RateLimitEnforcer($this->store),
            ),
            $this->reports,
        );
    }
}
