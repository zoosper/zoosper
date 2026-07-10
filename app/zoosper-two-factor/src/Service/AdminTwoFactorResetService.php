<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\TwoFactor\Repository\AdminTwoFactorResetRepository;

/**
 * Resets an admin user's 2FA state for re-enrolment.
 *
 * The service deliberately avoids accepting or returning OTPs, TOTP secrets,
 * recovery codes or provisioning URIs. A reset only removes protected 2FA state
 * and writes an audit-safe event without sensitive secret material.
 */
final readonly class AdminTwoFactorResetService
{
    public function __construct(
        private AdminTwoFactorResetRepository $resets,
        private ?AuditLogger $auditLogger = null,
    ) {
    }

    /**
     * Reset 2FA for the target admin user.
     */
    public function reset(int $targetAdminUserId, int $performedByAdminUserId): void
    {
        $this->resets->resetForAdminUser($targetAdminUserId);

        /*
         * Only non-secret IDs are included in the audit metadata. Do not include
         * OTP values, TOTP secrets, recovery-code plaintext or provisioning URIs.
         */
        $this->auditLogger?->record(
            actor: $performedByAdminUserId,
            action: 'admin_2fa.reset',
            entityType: 'admin_user',
            entityId: (string)$targetAdminUserId,
            metadata: [],
        );
    }
}
