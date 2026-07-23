<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Immutable rate limit rule definition.
 */
final readonly class RateLimitRule
{
    public function __construct(
        public string $key,
        public int $maxAttempts,
        public int $windowSeconds,
        public string $scope = 'default',
    ) {
        if ($key === '') {
            throw new \InvalidArgumentException('Rate limit rule key cannot be empty.');
        }

        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('Rate limit maxAttempts must be at least 1.');
        }

        if ($windowSeconds < 1) {
            throw new \InvalidArgumentException('Rate limit windowSeconds must be at least 1.');
        }
    }
}
