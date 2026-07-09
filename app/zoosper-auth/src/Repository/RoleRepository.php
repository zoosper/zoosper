<?php

declare(strict_types=1);

namespace Zoosper\Auth\Repository;

use PDO;
use RuntimeException;

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
        return $this->pdo->query('SELECT * FROM admin_permissions ORDER BY code ASC')->fetchAll();
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

    /** @param list<int> $permissionIds */
    public function createRole(string $code, string $label, array $permissionIds): int
    {
        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_roles (code, label, created_at, updated_at)
             VALUES (:code, :label, :created_at, :updated_at)'
        );
        $statement->execute([
            'code' => $this->normaliseCode($code),
            'label' => $label,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $roleId = (int) $this->pdo->lastInsertId();
        $this->syncPermissions($roleId, $permissionIds);
        return $roleId;
    }

    /** @param list<int> $permissionIds */
    public function updateRole(int $id, string $code, string $label, array $permissionIds): void
    {
        if ($this->findRoleById($id) === null) {
            throw new RuntimeException('Role does not exist: ' . $id);
        }

        $statement = $this->pdo->prepare(
            'UPDATE admin_roles SET code = :code, label = :label, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'code' => $this->normaliseCode($code),
            'label' => $label,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        $this->syncPermissions($id, $permissionIds);
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

    private function normaliseCode(string $code): string
    {
        $code = strtolower(trim($code));
        $code = preg_replace('/[^a-z0-9_]+/', '_', $code) ?: '';
        return trim($code, '_');
    }
}
