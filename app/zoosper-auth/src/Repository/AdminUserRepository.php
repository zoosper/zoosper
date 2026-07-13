<?php

declare(strict_types=1);

namespace Zoosper\Auth\Repository;

use PDO;
use RuntimeException;
use Zoosper\Auth\Model\AdminUser;

final readonly class AdminUserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<AdminUser> */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM admin_users ORDER BY id DESC');
        return array_map(fn (array $row): AdminUser => $this->hydrate($row), $statement->fetchAll());
    }

    /** @return list<AdminUser> */
    public function allForAssignment(): array
    {
        $statement = $this->pdo->query('SELECT * FROM admin_users ORDER BY name ASC, email ASC');
        return array_map(fn (array $row): AdminUser => $this->hydrate($row), $statement->fetchAll());
    }

    /** @return list<AdminUser> */
    public function search(string $term, int $limit = 50): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE email LIKE :term OR name LIKE :term ORDER BY name ASC, email ASC LIMIT :limit');
        $statement->bindValue('term', '%' . $term . '%');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return array_map(fn (array $row): AdminUser => $this->hydrate($row), $statement->fetchAll());
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

    /** @param list<int> $roleIds */
    public function createWithRoleIds(string $email, string $name, string $hash, string $status, array $roleIds, ?string $locale = null): int
    {
        if ($this->findByEmail($email) !== null) {
            throw new RuntimeException('Admin user already exists for email: ' . $email);
        }
        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare('INSERT INTO admin_users (email, name, password_hash, status, locale, created_at, updated_at) VALUES (:email, :name, :password_hash, :status, :locale, :created_at, :updated_at)');
        $statement->execute(['email' => mb_strtolower($email), 'name' => $name, 'password_hash' => $hash, 'status' => $status, 'created_at' => $now, 'updated_at' => $now]);
        $userId = (int) $this->pdo->lastInsertId();
        $this->syncRoles($userId, $roleIds);
        return $userId;
    }

    /** @param list<int> $roleIds */
    public function updateUser(int $id, string $email, string $name, string $status, array $roleIds, ?string $locale = null): void
    {
        if ($this->findById($id) === null) {
            throw new RuntimeException('Admin user does not exist: ' . $id);
        }
        $byEmail = $this->findByEmail($email);
        if ($byEmail !== null && $byEmail->id !== $id) {
            throw new RuntimeException('Another admin user already uses email: ' . $email);
        }
        $statement = $this->pdo->prepare('UPDATE admin_users SET email = :email, name = :name, status = :status, locale = :locale, updated_at = :updated_at WHERE id = :id');
        $statement->execute(['id' => $id, 'email' => mb_strtolower($email), 'name' => $name, 'status' => $status, 'updated_at' => gmdate('Y-m-d H:i:s')]);
        $this->syncRoles($id, $roleIds);
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
    private function hydrate(array $row): AdminUser
    {
        return new AdminUser((int) $row['id'], (string) $row['email'], (string) $row['name'], (string) $row['password_hash'], (string) $row['status'], $this->permissionsForUser((int) $row['id']),
            locale: isset($row['locale']) && is_string($row['locale']) && trim($row['locale']) !== '' ? trim($row['locale']) : null
        );
    }

    /** @return list<string> */
    private function permissionsForUser(int $userId): array
    {
        $statement = $this->pdo->prepare('SELECT DISTINCT p.code FROM admin_permissions p INNER JOIN admin_role_permissions rp ON rp.permission_id = p.id INNER JOIN admin_user_roles ur ON ur.role_id = rp.role_id WHERE ur.user_id = :user_id ORDER BY p.code');
        $statement->execute(['user_id' => $userId]);
        return array_map(static fn (array $row): string => (string) $row['code'], $statement->fetchAll());
    }
    /**
     * Updates only the admin interface locale for an existing admin user.
     *
     * A null locale intentionally means the configured admin locale should be
     * used. The caller is responsible for validating the locale format.
     */
    public function updateLocale(int $id, ?string $locale): void
    {
        $statement = $this->pdo->prepare('UPDATE admin_users SET locale = :locale WHERE id = :id');
        $statement->execute([
            'locale' => $locale,
            'id' => $id,
        ]);
    }
}
