<?php

declare(strict_types=1);

namespace Zoosper\Auth\AccountLockout;

final readonly class AdminAccountLockoutState
{
    public function __construct(
        public int $adminUserId,
        public int $failedAttempts,
        public ?string $lockedUntil,
        public ?string $lastFailedAt,
        public string $updatedAt,
    ) {
    }

    public function isLockedAt(int $now): bool
    {
        return $this->lockedUntil !== null && strtotime($this->lockedUntil . ' UTC') > $now;
    }
}
