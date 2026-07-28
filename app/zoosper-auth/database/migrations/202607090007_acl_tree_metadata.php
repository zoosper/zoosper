<?php

declare(strict_types=1);

use Zoosper\Core\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202607090007_acl_tree_metadata';
    }

    public function up(\PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            $this->addMysqlColumnIfMissing($pdo, 'admin_permissions', 'parent_code', 'VARCHAR(120) NULL');
            $this->addMysqlColumnIfMissing($pdo, 'admin_permissions', 'sort_order', 'INT NOT NULL DEFAULT 100');
        } else {
            $columns = $pdo->query('PRAGMA table_info(admin_permissions)')->fetchAll();
            $names = array_map(static fn (array $row): string => (string) $row['name'], $columns);
            if (!in_array('parent_code', $names, true)) {
                $pdo->exec('ALTER TABLE admin_permissions ADD COLUMN parent_code TEXT NULL');
            }
            if (!in_array('sort_order', $names, true)) {
                $pdo->exec('ALTER TABLE admin_permissions ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 100');
            }
        }

        $groups = [
            'page.manage' => ['content', 10],
            'user.manage' => ['users', 10],
            'role.manage' => ['users', 20],
            'settings.manage' => ['system', 90],
            'admin.access' => ['system', 1],
        ];

        $statement = $pdo->prepare('UPDATE admin_permissions SET parent_code = :parent_code, sort_order = :sort_order WHERE code = :code');
        foreach ($groups as $code => [$parentCode, $sortOrder]) {
            $statement->execute(['code' => $code, 'parent_code' => $parentCode, 'sort_order' => $sortOrder]);
        }
    }

    private function addMysqlColumnIfMissing(\PDO $pdo, string $table, string $column, string $definition): void
    {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column'
        );
        $statement->execute(['table' => $table, 'column' => $column]);
        if ((int) $statement->fetchColumn() === 0) {
            $pdo->exec(sprintf('ALTER TABLE %s ADD COLUMN %s %s', $table, $column, $definition));
        }
    }
};
