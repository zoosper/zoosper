<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Builds admin rate-limit contexts from safe caller identity parts.
 */
final class AdminRateLimitContextFactory
{
    public function __construct(
        private RateLimitIdentityHasher $hasher,
        private RateLimitRuntimeConfig $config,
    ) {
    }

    /** @param list<string> $identityParts */
    public function create(string $key, array $identityParts, ?int $now = null): RateLimitContext
    {
        return new RateLimitContext(
            $key,
            $this->hasher->hash($identityParts, $this->config->identitySalt),
            $now ?? time(),
        );
    }
}
