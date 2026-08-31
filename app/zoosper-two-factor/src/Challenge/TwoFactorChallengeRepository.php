<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Challenge;

use PDO;

/**
 * Persistence for login-time 2FA challenges, backed by the
 * `admin_two_factor_challenges` table.
 *
 * Security model:
 *  - only the SHA-256 hash of the challenge token is stored (never the plaintext);
 *  - challenges are single-use (consumed_at) and short-lived (expires_at);
 *  - lookups are by token hash, so a stolen DB row cannot be replayed as a token.
 *
 * Driver-aware: uses portable SQL that works on MySQL and SQLite (all datetime
 * comparisons are done with values computed in PHP, not DB date functions).
 *
 * Assumed columns (per zoosper-two-factor/config/db_schema.php):
 *   id, admin_user_id, challenge_token_hash, expires_at, consumed_at, created_at
 */
final readonly class TwoFactorChallengeRepository
{
    public function __construct(
        private PDO $pdo,
        private string $table = 'admin_two_factor_challenges',
    ) {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $this->table) !== 1) {
            throw new \InvalidArgumentException('Invalid challenge table name.');
        }
    }

    /**
     * Insert a new challenge row and return its id.
     */
    public function insert(int $adminUserId, string $tokenHash, string $expiresAt, string $createdAt): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO ' . $this->table
            . ' (admin_user_id, challenge_token_hash, expires_at, consumed_at, created_at)'
            . ' VALUES (:admin_user_id, :token_hash, :expires_at, NULL, :created_at)'
        );
        $statement->execute([
            'admin_user_id' => $adminUserId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => $createdAt,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Find a challenge by its token hash, or null when none exists.
     */
    public function findByTokenHash(string $tokenHash): ?TwoFactorChallenge
    {
        $statement = $this->pdo->prepare(
            'SELECT id, admin_user_id, challenge_token_hash, expires_at, consumed_at, created_at'
            . ' FROM ' . $this->table . ' WHERE challenge_token_hash = :token_hash LIMIT 1'
        );
        $statement->execute(['token_hash' => $tokenHash]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /**
     * Mark a challenge consumed. Returns true only if it was still unconsumed,
     * giving atomic single-use semantics even under concurrent requests.
     */
    public function markConsumed(int $id, string $consumedAt): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE ' . $this->table
            . ' SET consumed_at = :consumed_at'
            . ' WHERE id = :id AND consumed_at IS NULL'
        );
        $statement->execute(['id' => $id, 'consumed_at' => $consumedAt]);

        return $statement->rowCount() === 1;
    }

    /**
     * Delete all challenges for a user (e.g. on successful auth or logout).
     */
    public function deleteForUser(int $adminUserId): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM ' . $this->table . ' WHERE admin_user_id = :admin_user_id'
        );
        $statement->execute(['admin_user_id' => $adminUserId]);
    }

    /**
     * Delete expired/consumed challenges up to the given cutoff (housekeeping).
     * Rows are removed when they are already consumed OR their expiry has passed.
     */
    public function deleteExpired(string $cutoff): int
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM ' . $this->table
            . ' WHERE consumed_at IS NOT NULL OR expires_at < :cutoff'
        );
        $statement->execute(['cutoff' => $cutoff]);

        return $statement->rowCount();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): TwoFactorChallenge
    {
        return new TwoFactorChallenge(
            id: (int) $row['id'],
            adminUserId: (int) $row['admin_user_id'],
            tokenHash: (string) $row['challenge_token_hash'],
            expiresAt: (string) $row['expires_at'],
            consumedAt: isset($row['consumed_at']) && $row['consumed_at'] !== null
                ? (string) $row['consumed_at']
                : null,
            createdAt: (string) $row['created_at'],
        );
    }
}










