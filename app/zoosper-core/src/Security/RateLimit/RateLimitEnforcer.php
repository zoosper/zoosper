<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Coordinates policy decisions with a rate limit store.
 */
final class RateLimitEnforcer
{
    public function __construct(private RateLimitStoreInterface $store)
    {
    }

    public function check(RateLimitPolicy $policy, string $identityHash, int $now): RateLimitDecision
    {
        if (! $policy->enabled) {
            return RateLimitDecision::allow(0, 1);
        }

        if ($identityHash === '') {
            throw new \InvalidArgumentException('Rate limit identity hash cannot be empty.');
        }

        return $this->store->recordAttempt($policy->rule, $identityHash, $now);
    }
}
