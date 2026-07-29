<?php

declare(strict_types=1);

namespace Zoosper\Auth\Repository;

use PDO;
use RuntimeException;
use Throwable;

/**
 * BUG FIX (independently flagged by two reviewer passes, both incorrectly
 * stating this was "already fixed" elsewhere — it was not, see
 * AdminUserRepository.php's own matching fix in this same phase):
 * createRole()/updateRole() previously ran the role row write and the
 * permission/user-assignment sync as separate, un-transacted statements. A
 * failure partway through (DB connection drop, constraint violation) could
 * leave a role with no permissions synced, or a role row created with no
 * corresponding admin_role_permissions rows at all — silently inconsistent
 * data, not merely a missed edge case.
 *
 * Both methods now wrap their full write sequence in a transaction, using
 * the same beginTransaction()/commit()/catch(Throwable)+rollBack() pattern
 * already established elsewhere in this codebase (e.g.
 * DatabaseRateLimitStore::recordAttempt(),
 * AdminTwoFactorEnrollmentRepository::saveConfirmedEnrollment()) — no new
 * pattern introduced.
 */
final readonly class RoleRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function allRoles(): array
    {
        return $this->pdo->query('SELECT * FROM admin_roles ORDER BY label ASC')->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function allPermissions(): array
    {
        return $this->pdo->query('SELECT * FROM admin_permissions ORDER BY parent_code ASC, sort_order ASC, code ASC')->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findRoleById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_roles WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    /** @return list<int> */
    public function permissionIdsForRole(int $roleId): array
    {
        $statement = $this->pdo->prepare('SELECT permission_id FROM admin_role_permissions WHERE role_id = :role_id ORDER BY permission_id');
        $statement->execute(['role_id' => $roleId]);
        return array_map(static fn (array $row): int => (int) $row['permission_id'], $statement->fetchAll());
    }

    /** @return list<int> */
    public function userIdsForRole(int $roleId): array
    {
        $statement = $this->pdo->prepare('SELECT user_id FROM admin_user_roles WHERE role_id = :role_id ORDER BY user_id');
        $statement->execute(['role_id' => $roleId]);
        return array_map(static fn (array $row): int => (int) $row['user_id'], $statement->fetchAll());
    }

    /**
     * @param list<int> $permissionIds
     *
     * Wrapped in a transaction: the role row insert and its initial
     * permission sync now either both succeed or both roll back together.
     */
    public function createRole(string $code, string $label, array $permissionIds): int
    {
        $this->pdo->beginTransaction();

        try {
            $now = gmdate('Y-m-d H:i:s');
            $statement = $this->pdo->prepare('INSERT INTO admin_roles (code, label, created_at, updated_at) VALUES (:code, :label, :created_at, :updated_at)');
            $statement->execute(['code' => $this->normaliseCode($code), 'label' => $label, 'created_at' => $now, 'updated_at' => $now]);
            $roleId = (int) $this->pdo->lastInsertId();
            $this->syncPermissions($roleId, $permissionIds);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $roleId;
    }

    /**
     * @param list<int> $permissionIds
     * @param list<int>|null $userIds
     *
     * Wrapped in a transaction: the role row update, permission sync, and
     * (when provided) user-assignment sync now either all succeed or all
     * roll back together.
     */
    public function updateRole(int $id, string $code, string $label, array $permissionIds, ?array $userIds = null): void
    {
        if ($this->findRoleById($id) === null) {
            throw new RuntimeException('Role does not exist: ' . $id);
        }

        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare('UPDATE admin_roles SET code = :code, label = :label, updated_at = :updated_at WHERE id = :id');
            $statement->execute(['id' => $id, 'code' => $this->normaliseCode($code), 'label' => $label, 'updated_at' => gmdate('Y-m-d H:i:s')]);
            $this->syncPermissions($id, $permissionIds);

            if ($userIds !== null) {
                $this->syncUsersForRole($id, $userIds);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /** @param list<int> $permissionIds */
    private function syncPermissions(int $roleId, array $permissionIds): void
    {
        $permissionIds = array_values(array_unique(array_filter($permissionIds, static fn (int $id): bool => $id > 0)));
        $this->pdo->prepare('DELETE FROM admin_role_permissions WHERE role_id = :role_id')->execute(['role_id' => $roleId]);
        $statement = $this->pdo->prepare('INSERT INTO admin_role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)');
        foreach ($permissionIds as $permissionId) {
            $statement->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
        }
    }

    /** @param list<int> $userIds */
    private function syncUsersForRole(int $roleId, array $userIds): void
    {
        $userIds = array_values(array_unique(array_filter($userIds, static fn (int $id): bool => $id > 0)));
        $this->pdo->prepare('DELETE FROM admin_user_roles WHERE role_id = :role_id')->execute(['role_id' => $roleId]);
        $statement = $this->pdo->prepare('INSERT INTO admin_user_roles (user_id, role_id) VALUES (:user_id, :role_id)');
        foreach ($userIds as $userId) {
            $statement->execute(['user_id' => $userId, 'role_id' => $roleId]);
        }
    }

    private function normaliseCode(string $code): string
    {
        $code = strtolower(trim($code));
        $code = preg_replace('/[^a-z0-9_]+/', '_', $code) ?: '';
        return trim($code, '_');
    }
}
