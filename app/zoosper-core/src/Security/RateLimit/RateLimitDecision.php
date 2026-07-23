<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Result returned by a rate limit store after recording an attempt.
 */
final readonly class RateLimitDecision
{
    public function __construct(
        public bool $allowed,
        public int $attempts,
        public int $maxAttempts,
        public int $retryAfterSeconds,
    ) {
        if ($attempts < 0) {
            throw new \InvalidArgumentException('Rate limit attempts cannot be negative.');
        }

        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('Rate limit maxAttempts must be at least 1.');
        }

        if ($retryAfterSeconds < 0) {
            throw new \InvalidArgumentException('Rate limit retryAfterSeconds cannot be negative.');
        }
    }

    public static function allow(int $attempts, int $maxAttempts): self
    {
        return new self(true, $attempts, $maxAttempts, 0);
    }

    public static function deny(int $attempts, int $maxAttempts, int $retryAfterSeconds): self
    {
        return new self(false, $attempts, $maxAttempts, $retryAfterSeconds);
    }
}
