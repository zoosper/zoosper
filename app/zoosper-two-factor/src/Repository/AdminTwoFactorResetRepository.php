<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Repository;

use PDO;

/**
 * Deletes admin 2FA state for reset/re-enrolment flows.
 *
 * This repository never reads or logs TOTP secrets, OTP values or recovery-code
 * plaintext. It only deletes protected secret records, recovery-code hashes and
 * outstanding challenge tokens for a selected admin user.
 */
final readonly class AdminTwoFactorResetRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Delete all 2FA records for an admin user so they can enrol again.
     */
    public function resetForAdminUser(int $adminUserId): void
    {
        $this->pdo->beginTransaction();
        try {
            foreach ([
                'admin_user_two_factor',
                'admin_user_recovery_codes',
                'admin_two_factor_challenges',
            ] as $table) {
                $statement = $this->pdo->prepare('DELETE FROM ' . $table . ' WHERE admin_user_id = :admin_user_id');
                $statement->execute(['admin_user_id' => $adminUserId]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
