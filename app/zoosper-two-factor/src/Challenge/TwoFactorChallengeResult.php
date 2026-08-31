<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Challenge;

/**
 * Outcome of attempting to satisfy a 2FA challenge.
 */
final readonly class TwoFactorChallengeResult
{
    private function __construct(
        public bool $passed,
        public ?int $adminUserId,
        public string $reason, // 'ok' | 'wrong_code' | 'invalid_or_expired'
    ) {
    }

    public static function success(int $adminUserId): self
    {
        return new self(true, $adminUserId, 'ok');
    }

    public static function wrongCode(int $adminUserId): self
    {
        return new self(false, $adminUserId, 'wrong_code');
    }

    public static function invalidOrExpired(): self
    {
        return new self(false, null, 'invalid_or_expired');
    }
}










