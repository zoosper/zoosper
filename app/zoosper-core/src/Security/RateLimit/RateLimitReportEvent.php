<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Diagnostic event emitted by report-only rate-limit middleware.
 */
final readonly class RateLimitReportEvent
{
    public function __construct(
        public string $key,
        public string $identityHash,
        public bool $allowed,
        public int $attempts,
        public int $maxAttempts,
        public int $retryAfterSeconds,
        public int $now,
    ) {
    }

    public static function fromDecision(RateLimitContext $context, RateLimitDecision $decision): self
    {
        return new self(
            $context->key,
            $context->identityHash,
            $decision->allowed,
            $decision->attempts,
            $decision->maxAttempts,
            $decision->retryAfterSeconds,
            $context->now,
        );
    }
}
