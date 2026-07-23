<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Storage boundary for rate limit counters.
 *
 * Implementations should use opaque identity hashes and must not persist raw
 * secrets, raw tokens, raw passwords, or full sensitive request identifiers.
 */
interface RateLimitStoreInterface
{
    public function recordAttempt(RateLimitRule $rule, string $identityHash, int $now): RateLimitDecision;

    public function reset(RateLimitRule $rule, string $identityHash): void;
}
