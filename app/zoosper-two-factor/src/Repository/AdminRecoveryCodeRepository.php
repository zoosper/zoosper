<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Repository;

use PDO;

/**
 * Repository for admin 2FA recovery-code hashes.
 *
 * Plain recovery codes must never be stored. Only password-hash compatible
 * hashes are persisted and a code should be marked as used immediately after a
 * successful recovery flow.
 */
final readonly class AdminRecoveryCodeRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Replace all recovery-code hashes for an admin user.
     *
     * @param list<string> $hashes Password-hash compatible recovery-code hashes.
     */
    public function replaceForAdminUser(int $adminUserId, array $hashes): void
    {
        $this->pdo->beginTransaction();
        try {
            $delete = $this->pdo->prepare('DELETE FROM admin_user_recovery_codes WHERE admin_user_id = :admin_user_id');
            $delete->execute(['admin_user_id' => $adminUserId]);

            $insert = $this->pdo->prepare('INSERT INTO admin_user_recovery_codes (admin_user_id, code_hash, created_at) VALUES (:admin_user_id, :code_hash, CURRENT_TIMESTAMP)');
            foreach ($hashes as $hash) {
                $insert->execute([
                    'admin_user_id' => $adminUserId,
                    'code_hash' => $hash,
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
