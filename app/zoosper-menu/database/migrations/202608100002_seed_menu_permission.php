<?php

declare(strict_types=1);

use Zoosper\Core\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202608100002_seed_menu_permission';
    }

    public function up(PDO $pdo, string $driver): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $insertPermission = $driver === 'mysql'
            ? 'INSERT IGNORE INTO admin_permissions (code, label, parent_code, sort_order, created_at) VALUES (:code, :label, :parent_code, :sort_order, :created_at)'
            : 'INSERT OR IGNORE INTO admin_permissions (code, label, parent_code, sort_order, created_at) VALUES (:code, :label, :parent_code, :sort_order, :created_at)';
        $statement = $pdo->prepare($insertPermission);
        $statement->execute([
            'code' => 'menu.manage',
            'label' => 'Manage menus',
            'parent_code' => 'content',
            'sort_order' => 20,
            'created_at' => $now,
        ]);

        $permissionId = $this->idByCode($pdo, 'admin_permissions', 'menu.manage');
        foreach (['super_admin', 'content_admin'] as $roleCode) {
            $roleId = $this->idByCode($pdo, 'admin_roles', $roleCode);
            if ($roleId === 0 || $permissionId === 0) {
                continue;
            }
            $sql = $driver === 'mysql'
                ? 'INSERT IGNORE INTO admin_role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)'
                : 'INSERT OR IGNORE INTO admin_role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)';
            $grant = $pdo->prepare($sql);
            $grant->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
        }
    }

    private function idByCode(PDO $pdo, string $table, string $code): int
    {
        $statement = $pdo->prepare('SELECT id FROM ' . $table . ' WHERE code = :code');
        $statement->execute(['code' => $code]);
        $value = $statement->fetchColumn();

        return $value === false ? 0 : (int) $value;
    }
};
