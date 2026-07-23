<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Disabled-by-default provider for future middleware registration.
 */
final class RateLimitMiddlewareIntegration
{
    public function __construct(private RateLimitReportOnlyMiddlewareFactory $factory)
    {
    }

    /** @return list<ReportOnlyRateLimitMiddleware> */
    public function middleware(RateLimitRuntimeConfig $config): array
    {
        if (! $config->enabled) {
            return [];
        }

        if (! $config->isReportOnly()) {
            return [];
        }

        return [$this->factory->create($config)];
    }
}
