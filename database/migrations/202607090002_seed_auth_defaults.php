<?php

declare(strict_types=1);

use PDO;
use Zoosper\Core\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202607090002_seed_auth_defaults';
    }

    public function up(PDO $pdo, string $driver): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $permissions = [
            'admin.access' => 'Access admin area',
            'api.access' => 'Access API',
            'page.view' => 'View pages',
            'page.manage' => 'Manage pages',
            'role.manage' => 'Manage roles',
            'settings.manage' => 'Manage settings',
        ];

        foreach ($permissions as $code => $label) {
            $this->insertIgnore($pdo, 'admin_permissions', [
                'code' => $code,
                'label' => $label,
                'created_at' => $now,
            ]);
        }

        $roles = [
            'super_admin' => ['Super Admin', array_keys($permissions)],
            'content_admin' => ['Content Admin', ['admin.access', 'page.view', 'page.manage']],
            'api_consumer' => ['API Consumer', ['api.access']],
        ];

        foreach ($roles as $code => [$label, $rolePermissions]) {
            $this->insertIgnore($pdo, 'admin_roles', [
                'code' => $code,
                'label' => $label,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $roleId = $this->idByCode($pdo, 'admin_roles', $code);

            foreach ($rolePermissions as $permissionCode) {
                $permissionId = $this->idByCode($pdo, 'admin_permissions', $permissionCode);
                $this->insertIgnore($pdo, 'admin_role_permissions', [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function insertIgnore(PDO $pdo, string $table, array $data): void
    {
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT OR IGNORE INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', array_map(static fn (string $column): string => ':' . $column, $columns)),
        );

        if ((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('INSERT OR IGNORE', 'INSERT IGNORE', $sql);
        }

        $statement = $pdo->prepare($sql);
        $statement->execute($data);
    }

    private function idByCode(PDO $pdo, string $table, string $code): int
    {
        $statement = $pdo->prepare('SELECT id FROM ' . $table . ' WHERE code = :code');
        $statement->execute(['code' => $code]);

        return (int) $statement->fetchColumn();
    }
};
