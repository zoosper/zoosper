<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Repository;

use PDO;
use Zoosper\TwoFactor\Model\AdminTwoFactorProfile;

/**
 * Repository for admin two-factor authentication profiles.
 *
 * Repository methods never accept or return plain OTP values or recovery codes.
 * TOTP secrets must be protected ciphertext before being passed to persistence.
 */
final readonly class AdminTwoFactorRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByAdminUserId(int $adminUserId, string $method = 'totp'): ?AdminTwoFactorProfile
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM admin_user_two_factor WHERE admin_user_id = :admin_user_id AND method = :method LIMIT 1'
        );
        $statement->execute([
            'admin_user_id' => $adminUserId,
            'method' => $method,
        ]);

        $row = $statement->fetch();
        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function saveTotpProfile(int $adminUserId, string $secretCiphertext): void
    {
        $existing = $this->findByAdminUserId($adminUserId);

        if ($existing === null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO admin_user_two_factor (admin_user_id, method, secret_ciphertext, created_at, updated_at) VALUES (:admin_user_id, :method, :secret_ciphertext, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
            );
            $statement->execute([
                'admin_user_id' => $adminUserId,
                'method' => 'totp',
                'secret_ciphertext' => $secretCiphertext,
            ]);
            return;
        }

        $statement = $this->pdo->prepare(
            'UPDATE admin_user_two_factor SET secret_ciphertext = :secret_ciphertext, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $statement->execute([
            'id' => $existing->id,
            'secret_ciphertext' => $secretCiphertext,
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): AdminTwoFactorProfile
    {
        return new AdminTwoFactorProfile(
            id: (int) $row['id'],
            adminUserId: (int) $row['admin_user_id'],
            method: (string) $row['method'],
            secretCiphertext: (string) $row['secret_ciphertext'],
            enabledAt: isset($row['enabled_at']) ? (string) $row['enabled_at'] : null,
            lastVerifiedAt: isset($row['last_verified_at']) ? (string) $row['last_verified_at'] : null,
        );
    }
}
