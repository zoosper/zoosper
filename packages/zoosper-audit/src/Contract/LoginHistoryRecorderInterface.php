<?php

declare(strict_types=1);

namespace Zoosper\Audit\Contract;

/**
 * Contract for recording admin login-history events.
 *
 * Phase 1.41 (dependency graph correction): lets feature modules (currently
 * two-factor) record login-history rows without depending on zoosper/admin.
 * Admin's concrete LoginHistoryRepository implements this interface with no
 * signature changes — its existing record() method already matches exactly.
 *
 * Bound to the real LoginHistoryRepository by
 * app/zoosper-admin/config/services.php. If Admin is not installed, nothing
 * binds this interface, preserving the existing optional/graceful-degrade
 * pattern already used in AdminTwoFactorChallengeController.
 */
interface LoginHistoryRecorderInterface
{
    public function record(?int $adminUserId, string $email, string $status, ?string $ipAddress, ?string $userAgent): void;
}









