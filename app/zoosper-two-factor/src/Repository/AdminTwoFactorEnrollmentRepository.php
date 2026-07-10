<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Repository;

use PDO;

/**
 * Persists admin 2FA enrolment state.
 *
 * This repository stores protected/ciphertext secrets and recovery-code hashes
 * only. It must never store or log OTP values, raw TOTP secrets, provisioning
 * URIs, QR payloads or recovery-code plaintext.
 */
final readonly class AdminTwoFactorEnrollmentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function hasActiveEnrollment(int $adminUserId): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM admin_user_two_factor WHERE admin_user_id = :id AND enabled = 1');
        $statement->execute(['id' => $adminUserId]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function saveConfirmedEnrollment(int $adminUserId, string $protectedSecret, array $recoveryCodeHashes): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE FROM admin_user_two_factor WHERE admin_user_id = :id')->execute(['id' => $adminUserId]);
            $this->pdo->prepare('DELETE FROM admin_user_recovery_codes WHERE admin_user_id = :id')->execute(['id' => $adminUserId]);

            $statement = $this->pdo->prepare('INSERT INTO admin_user_two_factor (admin_user_id, method, secret_ciphertext, enabled, confirmed_at, created_at, updated_at) VALUES (:id, :method, :secret, 1, NOW(), NOW(), NOW())');
            $statement->execute([
                'id' => $adminUserId,
                'method' => 'totp',
                'secret' => $protectedSecret,
            ]);

            $insertCode = $this->pdo->prepare('INSERT INTO admin_user_recovery_codes (admin_user_id, code_hash, created_at) VALUES (:id, :hash, NOW())');
            foreach ($recoveryCodeHashes as $hash) {
                $insertCode->execute(['id' => $adminUserId, 'hash' => $hash]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
