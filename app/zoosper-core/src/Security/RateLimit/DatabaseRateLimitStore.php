<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

use PDO;

/**
 * Fixed-window database-backed rate limit store.
 *
 * The caller is responsible for passing an opaque, non-sensitive identity hash.
 *
 * SECURITY/CORRECTNESS FIX: recordAttempt() now uses an atomic per-driver
 * upsert instead of a SELECT-then-branch INSERT/UPDATE path. This prevents a
 * concurrent identical request from hitting the unique bucket constraint and
 * surfacing as an uncaught PDOException.
 */
final class DatabaseRateLimitStore implements RateLimitStoreInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function ensureSchema(): void
    {
        if (!$this->isSqlite()) {
            // MySQL installations receive this table through Core declarative
            // schema. Do not execute SQLite-only AUTOINCREMENT or CREATE INDEX
            // syntax on a production MySQL connection.
            return;
        }

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

        $this->upsertAttempt($rule, $identityHash, $windowStartsAt, $windowEndsAt, $now);
        $attempts = $this->currentAttempts($rule, $identityHash, $windowStartsAt);

        if ($attempts <= $rule->maxAttempts) {
            return RateLimitDecision::allow($attempts, $rule->maxAttempts);
        }

        return RateLimitDecision::deny($attempts, $rule->maxAttempts, max(0, $windowEndsAt - $now));
    }

    private function upsertAttempt(RateLimitRule $rule, string $identityHash, int $windowStartsAt, int $windowEndsAt, int $now): void
    {
        $params = [
            ':scope' => $rule->scope,
            ':identity_hash' => $identityHash,
            ':rule_key' => $rule->key,
            ':window_starts_at' => $windowStartsAt,
            ':window_ends_at' => $windowEndsAt,
            ':created_at' => $now,
            ':updated_at' => $now,
        ];

        if ($this->isSqlite()) {
            $statement = $this->pdo->prepare(
                'INSERT INTO rate_limit_buckets '
                . '(scope, identity_hash, rule_key, window_starts_at, window_ends_at, attempts, created_at, updated_at) '
                . 'VALUES (:scope, :identity_hash, :rule_key, :window_starts_at, :window_ends_at, 1, :created_at, :updated_at) '
                . 'ON CONFLICT(scope, identity_hash, rule_key, window_starts_at) '
                . 'DO UPDATE SET attempts = attempts + 1, updated_at = excluded.updated_at'
            );
        } else {
            $statement = $this->pdo->prepare(
                'INSERT INTO rate_limit_buckets '
                . '(scope, identity_hash, rule_key, window_starts_at, window_ends_at, attempts, created_at, updated_at) '
                . 'VALUES (:scope, :identity_hash, :rule_key, :window_starts_at, :window_ends_at, 1, :created_at, :updated_at) '
                . 'ON DUPLICATE KEY UPDATE attempts = attempts + 1, updated_at = VALUES(updated_at)'
            );
        }

        $statement->execute($params);
    }

    private function currentAttempts(RateLimitRule $rule, string $identityHash, int $windowStartsAt): int
    {
        $statement = $this->pdo->prepare(
            'SELECT attempts FROM rate_limit_buckets '
            . 'WHERE scope = :scope AND identity_hash = :identity_hash AND rule_key = :rule_key AND window_starts_at = :window_starts_at '
            . 'LIMIT 1'
        );
        $statement->execute([
            ':scope' => $rule->scope,
            ':identity_hash' => $identityHash,
            ':rule_key' => $rule->key,
            ':window_starts_at' => $windowStartsAt,
        ]);

        return (int) $statement->fetchColumn();
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

    private function isSqlite(): bool
    {
        return strtolower((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) === 'sqlite';
    }
}
