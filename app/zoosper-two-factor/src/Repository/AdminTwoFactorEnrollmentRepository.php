<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Repository;

use PDO;

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

    public function findProtectedSecret(int $adminUserId): ?string
    {
        $statement = $this->pdo->prepare(
            'SELECT secret_ciphertext FROM admin_user_two_factor WHERE admin_user_id = :id AND enabled = 1 LIMIT 1'
        );
        $statement->execute(['id' => $adminUserId]);
        $value = $statement->fetchColumn();

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function saveConfirmedEnrollment(int $adminUserId, string $protectedSecret, array $recoveryCodeHashes): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE FROM admin_user_two_factor WHERE admin_user_id = :id')->execute(['id' => $adminUserId]);
            $this->pdo->prepare('DELETE FROM admin_user_recovery_codes WHERE admin_user_id = :id')->execute(['id' => $adminUserId]);

            $statement = $this->pdo->prepare('INSERT INTO admin_user_two_factor (admin_user_id, method, secret_ciphertext, enabled, confirmed_at, created_at, updated_at) VALUES (:id, :method, :secret, 1, NOW(), NOW(), NOW())');
            $statement->execute(['id' => $adminUserId, 'method' => 'totp', 'secret' => $protectedSecret]);

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

    /**
     * KEY ROTATION FIX (confirmed 2026-07-30, real production lockout
     * incident): update ONLY the stored ciphertext for an admin's existing,
     * already-enabled TOTP enrolment — used by
     * AdminTwoFactorEnrollmentService::revealSecret()'s opportunistic
     * re-protection when a secret was decrypted via a previous encryption
     * key (see SecretProtector's own docblock for the full rotation
     * story). Deliberately narrower than saveConfirmedEnrollment(): this
     * must NEVER touch admin_user_recovery_codes or the enrolment's
     * confirmed_at/enabled state — it is re-encrypting the SAME already-
     * confirmed secret with a new key, not creating a new enrolment.
     */
    public function updateProtectedSecret(int $adminUserId, string $protectedSecret): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE admin_user_two_factor SET secret_ciphertext = :secret, updated_at = NOW() WHERE admin_user_id = :id AND enabled = 1'
        );
        $statement->execute(['id' => $adminUserId, 'secret' => $protectedSecret]);
    }
}
