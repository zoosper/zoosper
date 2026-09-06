<?php

declare(strict_types=1);

namespace Zoosper\Auth\PasswordReset;

use PDO;

/** Auth-owned persistence for single-use, hash-only Admin password reset tokens. */
final readonly class AdminPasswordResetTokenRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(
        int $adminUserId,
        string $publicId,
        string $tokenHash,
        string $expiresAt,
        string $createdAt,
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_password_reset_tokens '
            . '(public_id,admin_user_id,token_hash,expires_at,consumed_at,created_at) '
            . 'VALUES (:public_id,:admin_user_id,:token_hash,:expires_at,NULL,:created_at)',
        );
        $statement->execute([
            'public_id' => $publicId,
            'admin_user_id' => $adminUserId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => $createdAt,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByPublicId(string $publicId): ?AdminPasswordResetToken
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM admin_password_reset_tokens WHERE public_id = :public_id LIMIT 1',
        );
        $statement->execute(['public_id' => $publicId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function invalidateOutstandingForUser(int $adminUserId, string $consumedAt): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE admin_password_reset_tokens SET consumed_at = :consumed_at '
            . 'WHERE admin_user_id = :admin_user_id AND consumed_at IS NULL',
        );
        $statement->execute(['consumed_at' => $consumedAt, 'admin_user_id' => $adminUserId]);
    }

    public function consume(int $id, string $consumedAt): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE admin_password_reset_tokens SET consumed_at = :consumed_at '
            . 'WHERE id = :id AND consumed_at IS NULL',
        );
        $statement->execute(['consumed_at' => $consumedAt, 'id' => $id]);

        return $statement->rowCount() === 1;
    }

    /** @param array<string,mixed> $row */
    private function hydrate(array $row): AdminPasswordResetToken
    {
        return new AdminPasswordResetToken(
            (int) $row['id'],
            (string) $row['public_id'],
            (int) $row['admin_user_id'],
            (string) $row['token_hash'],
            (string) $row['expires_at'],
            isset($row['consumed_at']) ? (string) $row['consumed_at'] : null,
            (string) $row['created_at'],
        );
    }
}
