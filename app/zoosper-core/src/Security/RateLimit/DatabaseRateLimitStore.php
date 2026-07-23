<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

use PDO;

/**
 * Fixed-window database-backed rate limit store.
 *
 * The caller is responsible for passing an opaque, non-sensitive identity hash.
 */
final class DatabaseRateLimitStore implements RateLimitStoreInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Creates the rate_limit_buckets table when it does not already exist.
     *
     * This helper is intentionally explicit so tests and small deployments can
     * bootstrap the table without hiding schema ownership decisions.
     */
    public function ensureSchema(): void
    {
        $this->pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS rate_limit_buckets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    scope VARCHAR(120) NOT NULL,
    identity_hash VARCHAR(128) NOT NULL,
    rule_key VARCHAR(120) NOT NULL,
    window_starts_at INTEGER NOT NULL,
    window_ends_at INTEGER NOT NULL,
    attempts INTEGER NOT NULL DEFAULT 0,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL
)
SQL);

        $this->pdo->exec(
            'CREATE UNIQUE INDEX IF NOT EXISTS rate_limit_buckets_unique_window '
            . 'ON rate_limit_buckets (scope, identity_hash, rule_key, window_starts_at)'
        );

        $this->pdo->exec(
            'CREATE INDEX IF NOT EXISTS rate_limit_buckets_expires_idx '
            . 'ON rate_limit_buckets (window_ends_at)'
        );
    }

    public function recordAttempt(RateLimitRule $rule, string $identityHash, int $now): RateLimitDecision
    {
        if ($identityHash === '') {
            throw new \InvalidArgumentException('Rate limit identity hash cannot be empty.');
        }

        $windowStartsAt = intdiv($now, $rule->windowSeconds) * $rule->windowSeconds;
        $windowEndsAt = $windowStartsAt + $rule->windowSeconds;

        $this->pdo->beginTransaction();

        try {
            $select = $this->pdo->prepare(
                'SELECT id, attempts FROM rate_limit_buckets '
                . 'WHERE scope = :scope AND identity_hash = :identity_hash AND rule_key = :rule_key AND window_starts_at = :window_starts_at '
                . 'LIMIT 1'
            );
            $select->execute([
                ':scope' => $rule->scope,
                ':identity_hash' => $identityHash,
                ':rule_key' => $rule->key,
                ':window_starts_at' => $windowStartsAt,
            ]);

            $row = $select->fetch(PDO::FETCH_ASSOC);

            if (is_array($row)) {
                $attempts = ((int) $row['attempts']) + 1;
                $update = $this->pdo->prepare(
                    'UPDATE rate_limit_buckets SET attempts = :attempts, updated_at = :updated_at WHERE id = :id'
                );
                $update->execute([
                    ':attempts' => $attempts,
                    ':updated_at' => $now,
                    ':id' => (int) $row['id'],
                ]);
            } else {
                $attempts = 1;
                $insert = $this->pdo->prepare(
                    'INSERT INTO rate_limit_buckets '
                    . '(scope, identity_hash, rule_key, window_starts_at, window_ends_at, attempts, created_at, updated_at) '
                    . 'VALUES (:scope, :identity_hash, :rule_key, :window_starts_at, :window_ends_at, :attempts, :created_at, :updated_at)'
                );
                $insert->execute([
                    ':scope' => $rule->scope,
                    ':identity_hash' => $identityHash,
                    ':rule_key' => $rule->key,
                    ':window_starts_at' => $windowStartsAt,
                    ':window_ends_at' => $windowEndsAt,
                    ':attempts' => $attempts,
                    ':created_at' => $now,
                    ':updated_at' => $now,
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        if ($attempts <= $rule->maxAttempts) {
            return RateLimitDecision::allow($attempts, $rule->maxAttempts);
        }

        return RateLimitDecision::deny($attempts, $rule->maxAttempts, max(0, $windowEndsAt - $now));
    }

    public function reset(RateLimitRule $rule, string $identityHash): void
    {
        if ($identityHash === '') {
            throw new \InvalidArgumentException('Rate limit identity hash cannot be empty.');
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM rate_limit_buckets WHERE scope = :scope AND identity_hash = :identity_hash AND rule_key = :rule_key'
        );
        $statement->execute([
            ':scope' => $rule->scope,
            ':identity_hash' => $identityHash,
            ':rule_key' => $rule->key,
        ]);
    }

    public function deleteExpired(int $now): int
    {
        $statement = $this->pdo->prepare('DELETE FROM rate_limit_buckets WHERE window_ends_at <= :now');
        $statement->execute([':now' => $now]);

        return $statement->rowCount();
    }
}
