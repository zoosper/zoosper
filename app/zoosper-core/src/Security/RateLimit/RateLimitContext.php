<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Request-facing rate limit context passed to the guard seam.
 */
final readonly class RateLimitContext
{
    public function __construct(
        public string $key,
        public string $identityHash,
        public int $now,
    ) {
        if ($key === '') {
            throw new \InvalidArgumentException('Rate limit context key cannot be empty.');
        }

        if ($identityHash === '') {
            throw new \InvalidArgumentException('Rate limit identity hash cannot be empty.');
        }
    }
}
