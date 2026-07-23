<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Resolved rate limit policy for a route or context key.
 */
final readonly class RateLimitPolicy
{
    private function __construct(public bool $enabled, public ?RateLimitRule $rule = null)
    {
        if ($enabled && $rule === null) {
            throw new \InvalidArgumentException('Enabled rate limit policy requires a rule.');
        }
    }

    public static function disabled(): self
    {
        return new self(false);
    }

    public static function enabled(RateLimitRule $rule): self
    {
        return new self(true, $rule);
    }
}
