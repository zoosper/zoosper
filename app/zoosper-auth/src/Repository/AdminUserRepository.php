<?php

declare(strict_types=1);

namespace Zoosper\Auth\Repository;

use PDO;
use RuntimeException;
use Throwable;
use Zoosper\Auth\Model\AdminUser;

/**
 * Repository for admin users, roles and permissions.
 *
 * Phase 1.109: fixes the list-query N+1 by batch-loading permissions for
 * all()/search(), and by skipping permission loading entirely for
 * allForAssignment() where only id/name/email are needed.
 *
 * BUG FIX (this phase): createWithRoleIds()/updateUser() previously ran the
 * user row write and the role-assignment sync (syncRoles()) as separate,
 * un-transacted statements. Both reviewer passes that flagged this same
 * issue in RoleRepository stated it was "already fixed" here — it was not;
 * on inspection, this file had the identical unwrapped pattern. Both
 * methods now wrap their full write sequence in a transaction, using the
 * same pattern already established elsewhere in this codebase (e.g.
 * DatabaseRateLimitStore::recordAttempt()) and now also applied to
 * RoleRepository in this same phase.
 */
final readonly class AdminUserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function countByStatus(string $status): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM admin_users WHERE status = :status');
        $statement->execute(['status' => $status]);

        return (int) $statement->fetchColumn();
    }

    /** @return list<AdminUser> */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM admin_users ORDER BY id DESC');
        return $this->hydrateManyWithPermissions($statement->fetchAll());
    }

    /** @return list<AdminUser> */
    public function allForAssignment(?string $term = null, int $limit = 500): array
    {
        $sanitizedLimit = max(1, min($limit, 5000));

        if ($term !== null && trim($term) !== '') {
            $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE email LIKE :term OR name LIKE :term ORDER BY name ASC, email ASC LIMIT :limit');
            $statement->bindValue('term', '%' . trim($term) . '%');
            $statement->bindValue('limit', $sanitizedLimit, PDO::PARAM_INT);
            $statement->execute();
        } else {
            $statement = $this->pdo->prepare('SELECT * FROM admin_users ORDER BY name ASC, email ASC LIMIT :limit');
            $statement->bindValue('limit', $sanitizedLimit, PDO::PARAM_INT);
            $statement->execute();
        }

        return array_map(fn (array $row): AdminUser => $this->hydrateWithoutPermissions($row), $statement->fetchAll());
    }

    /**
     * @param list<int> $selectedIds
     * @return list<AdminUser>
     */
    public function findForAssignmentWithSelected(array $selectedIds, ?string $term = null, int $limit = 500): array
    {
        $users = $this->allForAssignment($term, $limit);
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->id] = $user;
        }

        $validSelectedIds = array_values(array_filter(
            $selectedIds,
            static fn (mixed $id): bool => is_int($id) && $id > 0,
        ));
        $missingSelected = array_values(array_diff($validSelectedIds, array_keys($userMap)));

        if ($missingSelected !== []) {
            $placeholders = implode(',', array_fill(0, count($missingSelected), '?'));
            $statement = $this->pdo->prepare("SELECT * FROM admin_users WHERE id IN ({$placeholders}) ORDER BY name ASC, email ASC");
            $statement->execute($missingSelected);
            foreach ($statement->fetchAll() as $row) {
                $user = $this->hydrateWithoutPermissions($row);
                $userMap[$user->id] = $user;
            }
        }

        $result = array_values($userMap);
        usort($result, static fn (AdminUser $a, AdminUser $b): int => [$a->name, $a->email] <=> [$b->name, $b->email]);

        return $result;
    }

    /** @return list<AdminUser> */
    public function search(string $term, int $limit = 50): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE email LIKE :term OR name LIKE :term ORDER BY name ASC, email ASC LIMIT :limit');
        $statement->bindValue('term', '%' . $term . '%');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $this->hydrateManyWithPermissions($statement->fetchAll());
    }

    public function findByEmail(string $email): ?AdminUser
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => mb_strtolower($email)]);
        $row = $statement->fetch();
        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findById(int $id): ?AdminUser
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function create(string $email, string $name, string $hash, string $roleCode = 'super_admin'): int
    {
        $roleId = $this->roleId($roleCode);
        return $this->createWithRoleIds($email, $name, $hash, 'active', [$roleId]);
    }

    /**
     * @param list<int> $roleIds
     *
     * Wrapped in a transaction: the user row insert and its initial role
     * assignment now either both succeed or both roll back together.
     */
    public function createWithRoleIds(string $email, string $name, string $hash, string $status, array $roleIds, ?string $locale = null): int
    {
        if ($this->findByEmail($email) !== null) {
            throw new RuntimeException('Admin user already exists for email: ' . $email);
        }

        $this->pdo->beginTransaction();

        try {
            $now = gmdate('Y-m-d H:i:s');
            $statement = $this->pdo->prepare('INSERT INTO admin_users (email, name, password_hash, status, locale, created_at, updated_at) VALUES (:email, :name, :password_hash, :status, :locale, :created_at, :updated_at)');
            $statement->execute(['email' => mb_strtolower($email), 'name' => $name, 'password_hash' => $hash, 'status' => $status, 'locale' => $locale, 'created_at' => $now, 'updated_at' => $now]);
            $userId = (int) $this->pdo->lastInsertId();
            $this->syncRoles($userId, $roleIds);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $userId;
    }

    /**
     * @param list<int> $roleIds
     *
     * Wrapped in a transaction: the user row update and role-assignment
     * sync now either both succeed or both roll back together.
     */
    public function updateUser(int $id, string $email, string $name, string $status, array $roleIds, ?string $locale = null): void
    {
        if ($this->findById($id) === null) {
            throw new RuntimeException('Admin user does not exist: ' . $id);
        }

        $byEmail = $this->findByEmail($email);
        if ($byEmail !== null && $byEmail->id !== $id) {
            throw new RuntimeException('Another admin user already uses email: ' . $email);
        }

        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare('UPDATE admin_users SET email = :email, name = :name, status = :status, locale = :locale, updated_at = :updated_at WHERE id = :id');
            $statement->execute(['id' => $id, 'email' => mb_strtolower($email), 'name' => $name, 'status' => $status, 'locale' => $locale, 'updated_at' => gmdate('Y-m-d H:i:s')]);
            $this->syncRoles($id, $roleIds);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function updatePassword(int $id, string $hash): void
    {
        $statement = $this->pdo->prepare('UPDATE admin_users SET password_hash = :password_hash, updated_at = :updated_at WHERE id = :id');
        $statement->execute(['id' => $id, 'password_hash' => $hash, 'updated_at' => gmdate('Y-m-d H:i:s')]);
    }

    public function updateLastLogin(int $id): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare('UPDATE admin_users SET last_login_at = :last_login_at, updated_at = :updated_at WHERE id = :id');
        $statement->execute(['id' => $id, 'last_login_at' => $now, 'updated_at' => $now]);
    }

    /** @return list<int> */
    public function roleIdsForUser(int $userId): array
    {
        $statement = $this->pdo->prepare('SELECT role_id FROM admin_user_roles WHERE user_id = :user_id ORDER BY role_id');
        $statement->execute(['user_id' => $userId]);
        return array_map(static fn (array $row): int => (int) $row['role_id'], $statement->fetchAll());
    }

    /** @param list<int> $roleIds */
    private function syncRoles(int $userId, array $roleIds): void
    {
        $roleIds = array_values(array_unique(array_filter($roleIds, static fn (int $id): bool => $id > 0)));
        if ($roleIds === []) {
            throw new RuntimeException('At least one role must be selected.');
        }
        $this->pdo->prepare('DELETE FROM admin_user_roles WHERE user_id = :user_id')->execute(['user_id' => $userId]);
        $statement = $this->pdo->prepare('INSERT INTO admin_user_roles (user_id, role_id) VALUES (:user_id, :role_id)');
        foreach ($roleIds as $roleId) {
            $statement->execute(['user_id' => $userId, 'role_id' => $roleId]);
        }
    }

    private function roleId(string $roleCode): int
    {
        $statement = $this->pdo->prepare('SELECT id FROM admin_roles WHERE code = :code');
        $statement->execute(['code' => $roleCode]);
        $id = $statement->fetchColumn();
        if ($id === false) {
            throw new RuntimeException('Role does not exist: ' . $roleCode);
        }
        return (int) $id;
    }

    /** @param array<string, mixed> $row */

    public function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw new \InvalidArgumentException('Unsupported Admin User status.');
        }
        $statement = $this->pdo->prepare('UPDATE admin_users SET status = :status, updated_at = :updated_at WHERE id = :id');
        $statement->execute(['id' => $id, 'status' => $status, 'updated_at' => gmdate('Y-m-d H:i:s')]);
        if ($statement->rowCount() !== 1) {
            throw new \RuntimeException('Admin User status update did not affect exactly one row.');
        }
    }

    private function hydrate(array $row): AdminUser
    {
        return new AdminUser(
            (int) $row['id'],
            (string) $row['email'],
            (string) $row['name'],
            (string) $row['password_hash'],
            (string) $row['status'],
            $this->permissionsForUser((int) $row['id']),
            locale: isset($row['locale']) && is_string($row['locale']) && trim($row['locale']) !== '' ? trim($row['locale']) : null,
        );
    }

    /** @param array<string, mixed> $row @param array<int, list<string>> $permissionsByUserId */
    private function hydrateWithPermissionsMap(array $row, array $permissionsByUserId): AdminUser
    {
        $id = (int) $row['id'];
        return new AdminUser(
            $id,
            (string) $row['email'],
            (string) $row['name'],
            (string) $row['password_hash'],
            (string) $row['status'],
            $permissionsByUserId[$id] ?? [],
            locale: isset($row['locale']) && is_string($row['locale']) && trim($row['locale']) !== '' ? trim($row['locale']) : null,
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrateWithoutPermissions(array $row): AdminUser
    {
        return new AdminUser(
            (int) $row['id'],
            (string) $row['email'],
            (string) $row['name'],
            (string) $row['password_hash'],
            (string) $row['status'],
            [],
            locale: isset($row['locale']) && is_string($row['locale']) && trim($row['locale']) !== '' ? trim($row['locale']) : null,
        );
    }

    /** @param list<array<string, mixed>> $rows @return list<AdminUser> */
    private function hydrateManyWithPermissions(array $rows): array
    {
        if ($rows === []) {
            return [];
        }
        $userIds = array_map(static fn (array $row): int => (int) $row['id'], $rows);
        $permissionsByUserId = $this->permissionsForUserIds($userIds);
        return array_map(fn (array $row): AdminUser => $this->hydrateWithPermissionsMap($row, $permissionsByUserId), $rows);
    }

    /** @return list<string> */
    private function permissionsForUser(int $userId): array
    {
        $statement = $this->pdo->prepare('SELECT DISTINCT p.code FROM admin_permissions p INNER JOIN admin_role_permissions rp ON rp.permission_id = p.id INNER JOIN admin_user_roles ur ON ur.role_id = rp.role_id WHERE ur.user_id = :user_id ORDER BY p.code');
        $statement->execute(['user_id' => $userId]);
        return array_map(static fn (array $row): string => (string) $row['code'], $statement->fetchAll());
    }

    /** @param list<int> $userIds @return array<int, list<string>> */
    private function permissionsForUserIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if ($userIds === []) {
            return [];
        }
        $placeholders = [];
        $params = [];
        foreach ($userIds as $index => $userId) {
            $key = ':uid' . $index;
            $placeholders[] = $key;
            $params[$key] = $userId;
        }
        $sql = 'SELECT DISTINCT ur.user_id AS user_id, p.code AS code'
            . ' FROM admin_permissions p'
            . ' INNER JOIN admin_role_permissions rp ON rp.permission_id = p.id'
            . ' INNER JOIN admin_user_roles ur ON ur.role_id = rp.role_id'
            . ' WHERE ur.user_id IN (' . implode(', ', $placeholders) . ')'
            . ' ORDER BY ur.user_id, p.code';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        $grouped = [];
        foreach ($statement->fetchAll() as $row) {
            $grouped[(int) $row['user_id']][] = (string) $row['code'];
        }
        return $grouped;
    }

    public function updateLocale(int $id, ?string $locale): void
    {
        $statement = $this->pdo->prepare('UPDATE admin_users SET locale = :locale WHERE id = :id');
        $statement->execute(['locale' => $locale, 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM admin_users WHERE id = :id');
        $statement->execute(['id' => $id]);

        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('Admin User deletion did not affect exactly one row.');
        }
    }
}










