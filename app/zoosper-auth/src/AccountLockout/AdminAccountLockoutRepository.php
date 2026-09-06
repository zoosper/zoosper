<?php

declare(strict_types=1);

namespace Zoosper\Auth\AccountLockout;

use PDO;

/** Auth-owned atomic persistence for per-account failed-login state. */
final readonly class AdminAccountLockoutRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $adminUserId): ?AdminAccountLockoutState
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_account_lockouts WHERE admin_user_id = :admin_user_id LIMIT 1');
        $statement->execute(['admin_user_id' => $adminUserId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? new AdminAccountLockoutState(
            (int) $row['admin_user_id'],
            (int) $row['failed_attempts'],
            isset($row['locked_until']) ? (string) $row['locked_until'] : null,
            isset($row['last_failed_at']) ? (string) $row['last_failed_at'] : null,
            (string) $row['updated_at'],
        ) : null;
    }

    public function recordFailure(int $adminUserId, int $threshold, string $failedAt, string $lockedUntil): AdminAccountLockoutState
    {
        $sqlite = strtolower((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) === 'sqlite';
        if ($sqlite) {
            $sql = 'INSERT INTO admin_account_lockouts (admin_user_id,failed_attempts,locked_until,last_failed_at,updated_at) '
                . 'VALUES (:admin_user_id,1,CASE WHEN :initial_threshold <= 1 THEN :initial_locked_until ELSE NULL END,:failed_at,:updated_at) '
                . 'ON CONFLICT(admin_user_id) DO UPDATE SET '
                . 'failed_attempts = failed_attempts + 1, '
                . 'locked_until = CASE WHEN failed_attempts + 1 >= :threshold THEN :locked_until ELSE locked_until END, '
                . 'last_failed_at = :failed_at_update, updated_at = :updated_at_update';
        } else {
            $sql = 'INSERT INTO admin_account_lockouts (admin_user_id,failed_attempts,locked_until,last_failed_at,updated_at) '
                . 'VALUES (:admin_user_id,1,CASE WHEN :initial_threshold <= 1 THEN :initial_locked_until ELSE NULL END,:failed_at,:updated_at) '
                . 'ON DUPLICATE KEY UPDATE '
                . 'failed_attempts = failed_attempts + 1, '
                . 'locked_until = IF(failed_attempts >= :threshold, :locked_until, locked_until), '
                . 'last_failed_at = :failed_at_update, updated_at = :updated_at_update';
        }
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue('admin_user_id', $adminUserId, PDO::PARAM_INT);
        $statement->bindValue('initial_threshold', $threshold, PDO::PARAM_INT);
        $statement->bindValue('initial_locked_until', $lockedUntil);
        $statement->bindValue('failed_at', $failedAt);
        $statement->bindValue('updated_at', $failedAt);
        $statement->bindValue('threshold', $threshold, PDO::PARAM_INT);
        $statement->bindValue('locked_until', $lockedUntil);
        $statement->bindValue('failed_at_update', $failedAt);
        $statement->bindValue('updated_at_update', $failedAt);
        $statement->execute();
        return $this->find($adminUserId) ?? throw new \RuntimeException('Admin lockout state was not persisted.');
    }

    public function clear(int $adminUserId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM admin_account_lockouts WHERE admin_user_id = :admin_user_id');
        $statement->execute(['admin_user_id' => $adminUserId]);
    }
}
