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
        $now = gmdate('Y-m-d H:i:s');

        $statement = $this->pdo->prepare(
            'INSERT INTO admin_users (email, name, password_hash, status, created_at, updated_at)
             VALUES (:email, :name, :password_hash, :status, :created_at, :updated_at)'
        );
        $statement->execute([
            'email' => mb_strtolower($email),
            'name' => $name,
            'password_hash' => $hash,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $userId = (int) $this->pdo->lastInsertId();
        $this->assignRole($userId, $roleCode);

        return $userId;
    }

    public function updateLastLogin(int $id): void
    {
        $now = gmdate('Y-m-d H:i:s');

        $statement = $this->pdo->prepare(
            'UPDATE admin_users
             SET last_login_at = :last_login_at,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'last_login_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function assignRole(int $userId, string $roleCode): void
    {
        $roleId = $this->roleId($roleCode);

        $statement = $this->pdo->prepare(
            'INSERT INTO admin_user_roles (user_id, role_id) VALUES (:user_id, :role_id)'
        );
        $statement->execute([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): AdminUser
    {
        return new AdminUser(
            id: (int) $row['id'],
            email: (string) $row['email'],
            name: (string) $row['name'],
            passwordHash: (string) $row['password_hash'],
            status: (string) $row['status'],
            permissions: $this->permissionsForUser((int) $row['id']),
        );
    }

    /**
     * @return list<string>
     */
    private function permissionsForUser(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT DISTINCT p.code
             FROM admin_permissions p
             INNER JOIN admin_role_permissions rp ON rp.permission_id = p.id
             INNER JOIN admin_user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = :user_id
             ORDER BY p.code'
        );
        $statement->execute(['user_id' => $userId]);

        return array_map(
            static fn (array $row): string => (string) $row['code'],
            $statement->fetchAll(),
        );
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
}
