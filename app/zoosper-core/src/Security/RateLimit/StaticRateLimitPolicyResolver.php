<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Deterministic policy resolver backed by an in-memory map.
 */
final class StaticRateLimitPolicyResolver implements RateLimitPolicyResolverInterface
{
    /** @param array<string, RateLimitRule> $rules */
    public function __construct(private array $rules = [])
    {
    }

    public function resolve(string $key): RateLimitPolicy
    {
        if ($key === '' || ! isset($this->rules[$key])) {
            return RateLimitPolicy::disabled();
        }

        return RateLimitPolicy::enabled($this->rules[$key]);
    }
}
