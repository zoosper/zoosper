<?php

declare(strict_types=1);

use Zoosper\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202608020001_seed_store_order_permissions';
    }

    public function up(\PDO $pdo, string $driver): void
    {
        if (!in_array($driver, ['mysql', 'sqlite'], true)) {
            throw new \RuntimeException('Unsupported database driver for Store Orders permission migration: ' . $driver);
        }

        $permissions = [
            'store_order.view' => [
                'label' => 'View store orders',
                'parent_code' => 'content',
                'sort_order' => 30,
            ],
            'store_order.export' => [
                'label' => 'Export store orders',
                'parent_code' => 'content',
                'sort_order' => 40,
            ],
        ];

        $select = $pdo->prepare(
            'SELECT id FROM admin_permissions WHERE code = :code LIMIT 1',
        );
        $insert = $pdo->prepare(
            'INSERT INTO admin_permissions '
            . '(code, label, created_at, parent_code, sort_order) '
            . 'VALUES (:code, :label, :created_at, :parent_code, :sort_order)',
        );
        $update = $pdo->prepare(
            'UPDATE admin_permissions '
            . 'SET label = :label, parent_code = :parent_code, sort_order = :sort_order '
            . 'WHERE code = :code',
        );

        foreach ($permissions as $code => $definition) {
            $select->execute(['code' => $code]);
            $parameters = [
                'code' => $code,
                'label' => $definition['label'],
                'parent_code' => $definition['parent_code'],
                'sort_order' => $definition['sort_order'],
            ];

            if ($select->fetchColumn() === false) {
                $insert->execute([
                    ...$parameters,
                    'created_at' => gmdate('Y-m-d H:i:s'),
                ]);
                continue;
            }

            $update->execute($parameters);
        }

        $verify = $pdo->query(
            "SELECT COUNT(*) FROM admin_permissions "
            . "WHERE code IN ('store_order.view', 'store_order.export') "
            . "AND parent_code = 'content'",
        );
        if ((int) $verify->fetchColumn() !== 2) {
            throw new \RuntimeException('Store Orders permissions were not seeded correctly.');
        }
    }
};











