<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Request-facing rate limit guard seam.
 */
final class RateLimitGuard
{
    public function __construct(
        private RateLimitPolicyResolverInterface $policies,
        private RateLimitEnforcer $enforcer,
    ) {
    }

    public function check(RateLimitContext $context): RateLimitDecision
    {
        return $this->enforcer->check(
            $this->policies->resolve($context->key),
            $context->identityHash,
            $context->now,
        );
    }
}
