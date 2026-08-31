<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Repository;

use PDO;

final readonly class AdminRecoveryCodeRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function replaceForAdminUser(int $adminUserId, array $hashes): void
    {
        $this->pdo->beginTransaction();
        try {
            $delete = $this->pdo->prepare('DELETE FROM admin_user_recovery_codes WHERE admin_user_id = :admin_user_id');
            $delete->execute(['admin_user_id' => $adminUserId]);

            $insert = $this->pdo->prepare('INSERT INTO admin_user_recovery_codes (admin_user_id, code_hash, created_at) VALUES (:admin_user_id, :code_hash, CURRENT_TIMESTAMP)');
            foreach ($hashes as $hash) {
                $insert->execute(['admin_user_id' => $adminUserId, 'code_hash' => $hash]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function redeem(int $adminUserId, string $code, ?string $usedAt = null): bool
    {
        $code = trim($code);
        if ($code === '') {
            return false;
        }

        $select = $this->pdo->prepare('SELECT id, code_hash FROM admin_user_recovery_codes WHERE admin_user_id = :id AND used_at IS NULL');
        $select->execute(['id' => $adminUserId]);
        $usedAt ??= gmdate('Y-m-d H:i:s');

        foreach ($select->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hash = (string) ($row['code_hash'] ?? '');
            if ($hash === '' || !password_verify($code, $hash)) {
                continue;
            }

            $update = $this->pdo->prepare('UPDATE admin_user_recovery_codes SET used_at = :used_at WHERE id = :id AND used_at IS NULL');
            $update->execute(['used_at' => $usedAt, 'id' => (int) $row['id']]);

            return $update->rowCount() === 1;
        }

        return false;
    }
}










