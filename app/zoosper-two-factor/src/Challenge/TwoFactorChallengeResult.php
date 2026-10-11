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

    /**
     * Return the authenticated user identity only for a successful challenge.
     *
     * The result factories keep failure details available to callers, while this
     * accessor makes the success-only identity invariant explicit and fail closed.
     */
    public function successfulAdminUserId(): int
    {
        if (!$this->passed || $this->adminUserId === null) {
            throw new \LogicException('A failed two-factor challenge has no successful Admin User identity.');
        }

        return $this->adminUserId;
    }
}










