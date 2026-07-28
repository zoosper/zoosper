<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

use Throwable;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\TwoFactor\Repository\AdminTwoFactorResetRepository;

/**
 * Resets an admin user's 2FA state for re-enrolment.
 *
 * The service deliberately avoids accepting or returning OTPs, TOTP secrets,
 * recovery codes or provisioning URIs. A reset only removes protected 2FA state
 * and writes an audit-safe event without sensitive secret material.
 *
 * Phase 1.41 (dependency graph correction): now depends on
 * Zoosper\Core\Audit\AuditLoggerInterface instead of the concrete
 * Zoosper\Admin\Audit\AuditLogger — removes this module's need for
 * zoosper/admin to be installed at all.
 *
 * BUG FIX (discovered during this phase): the previous auditReset() call
 * passed an int as the `actor` named argument to AuditLogger::record(),
 * which requires a `?AdminUser $actor` and a required `string $summary`
 * that was never supplied. Under strict_types=1 this threw a TypeError on
 * every single call, silently swallowed by the catch(Throwable) below — so
 * 2FA reset actions were NEVER actually recorded in the audit log. The new
 * AuditLoggerInterface::logAction() signature matches what this service
 * actually has available (a plain admin-user id), so this call now
 * succeeds correctly.
 */
final readonly class AdminTwoFactorResetService
{
    public function __construct(
        private AdminTwoFactorResetRepository $resets,
        private ?AuditLoggerInterface $auditLogger = null,
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
     * Record a non-sensitive audit event when an AuditLoggerInterface is
     * available (i.e. an admin/audit-providing module is installed).
     *
     * This method intentionally avoids logging OTP values, TOTP secrets,
     * recovery-code plaintext, provisioning URIs and QR data. It also avoids
     * throwing if the audit logger implementation misbehaves, because the
     * reset itself has already completed successfully and should not be
     * rolled back because of best-effort audit logging.
     */
    private function auditReset(int $targetAdminUserId, int $performedByAdminUserId): void
    {
        if ($this->auditLogger === null) {
            return;
        }

        try {
            $this->auditLogger->logAction(
                actorAdminUserId: $performedByAdminUserId,
                actorEmail: null,
                action: 'admin_2fa.reset',
                entityType: 'admin_user',
                entityId: (string) $targetAdminUserId,
                summary: 'Reset two-factor authentication for admin user #' . $targetAdminUserId,
                metadata: [],
            );
        } catch (Throwable) {
            /*
             * Audit logging must be best-effort. Never expose or log secret
             * values here.
             */
        }
    }
}
