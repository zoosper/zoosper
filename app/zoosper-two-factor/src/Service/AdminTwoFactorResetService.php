<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

use Throwable;
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
        $this->auditReset($targetAdminUserId, $performedByAdminUserId);
    }

    /**
     * Record a non-sensitive audit event when the current AuditLogger supports it.
     *
     * This method intentionally avoids logging OTP values, TOTP secrets,
     * recovery-code plaintext, provisioning URIs and QR data. It also avoids
     * throwing if an older logger signature is present, because the reset itself
     * has already completed successfully and should not be rolled back because
     * of optional audit logging compatibility.
     */
    private function auditReset(int $targetAdminUserId, int $performedByAdminUserId): void
    {
        if ($this->auditLogger === null || !method_exists($this->auditLogger, 'record')) {
            return;
        }

        try {
            $this->auditLogger->record(
                actor: $performedByAdminUserId,
                action: 'admin_2fa.reset',
                entityType: 'admin_user',
                entityId: (string)$targetAdminUserId,
                metadata: [],
            );
        } catch (Throwable) {
            /*
             * Audit logging must be best-effort until the logger contract is
             * formalised. Never expose or log secret values here.
             */
        }
    }
}
