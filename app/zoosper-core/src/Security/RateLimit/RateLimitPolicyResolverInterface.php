<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Resolves rate limit policy for a route or context key.
 */
interface RateLimitPolicyResolverInterface
{
    public function resolve(string $key): RateLimitPolicy;
}
