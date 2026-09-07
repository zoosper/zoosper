<?php

declare(strict_types=1);

namespace Zoosper\Auth\AccountLockout;

/** Applies temporary lockout policy without changing active/inactive account status. */
final readonly class AdminAccountLockoutService
{
    public function __construct(
        private AdminAccountLockoutRepository $repository,
        private int $maxAttempts = 5,
        private int $lockoutSeconds = 900,
        private ?\Closure $clock = null,
    ) {
    }

    public function state(int $adminUserId): ?AdminAccountLockoutState
    {
        return $this->repository->find($adminUserId);
    }

    public function isLocked(int $adminUserId): bool
    {
        $state = $this->repository->find($adminUserId);
        if ($state === null) {
            return false;
        }
        if (!$state->isLockedAt($this->now()) && $state->lockedUntil !== null) {
            $this->repository->clear($adminUserId);
            return false;
        }
        return $state->isLockedAt($this->now());
    }

    public function recordFailure(int $adminUserId): AdminAccountLockoutState
    {
        $now = $this->now();
        return $this->repository->recordFailure(
            $adminUserId,
            max(1, $this->maxAttempts),
            gmdate('Y-m-d H:i:s', $now),
            gmdate('Y-m-d H:i:s', $now + max(60, $this->lockoutSeconds)),
        );
    }

    public function clear(int $adminUserId): void
    {
        $this->repository->clear($adminUserId);
    }

    private function now(): int
    {
        return $this->clock !== null ? (int) ($this->clock)() : time();
    }
}
