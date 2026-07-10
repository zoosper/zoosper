<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Repository;

use PDO;
use RuntimeException;

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
     *
     * Missing tables are ignored so CLI diagnostics remain safe on fresh
     * development databases where the two-factor module schema has not yet been
     * applied. Existing tables are still cleaned transactionally.
     */
    public function resetForAdminUser(int $adminUserId): void
    {
        $tables = array_values(array_filter([
            'admin_user_two_factor',
            'admin_user_recovery_codes',
            'admin_two_factor_challenges',
        ], fn (string $table): bool => $this->tableExists($table)));

        if ($tables === []) {
            return;
        }

        $this->pdo->beginTransaction();
        try {
            foreach ($tables as $table) {
                $statement = $this->pdo->prepare('DELETE FROM ' . $this->quoteIdentifier($table) . ' WHERE admin_user_id = :admin_user_id');
                $statement->execute(['admin_user_id' => $adminUserId]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * Return missing 2FA tables for diagnostics.
     *
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(array_filter([
            'admin_user_two_factor',
            'admin_user_recovery_codes',
            'admin_two_factor_challenges',
        ], fn (string $table): bool => !$this->tableExists($table)));
    }

    /**
     * Determine whether a table exists in the active database.
     */
    private function tableExists(string $table): bool
    {
        $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $statement = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table LIMIT 1");
            $statement->execute(['table' => $table]);
            return (bool) $statement->fetchColumn();
        }

        $statement = $this->pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1');
        $statement->execute(['table' => $table]);
        return (bool) $statement->fetchColumn();
    }

    /**
     * Quote a safe SQL identifier.
     */
    private function quoteIdentifier(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new RuntimeException('Unsafe SQL identifier: ' . $identifier);
        }

        return (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
            ? '"' . $identifier . '"'
            : '`' . $identifier . '`';
    }
}
