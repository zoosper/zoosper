<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

use PDO;

/**
 * Manages module state persistence in the database.
 */
final readonly class ModuleRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<string, string> Map module name to status */
    public function allStatuses(): array
    {
        $statement = $this->pdo->query('SELECT name, status FROM modules');
        $results = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $results[(string) $row['name']] = (string) $row['status'];
        }

        return $results;
    }

    public function setStatus(string $name, string $status): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $statement = $this->pdo->prepare(
                'INSERT INTO modules (name, status, created_at, updated_at)
                 VALUES (:name, :status, :created_at, :updated_at)
                 ON DUPLICATE KEY UPDATE status = :status_update, updated_at = :updated_at_update'
            );

            $statement->execute([
                'name' => $name,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
                'status_update' => $status,
                'updated_at_update' => $now,
            ]);
            return;
        }

        $this->pdo->prepare('INSERT OR REPLACE INTO modules (name, status, created_at, updated_at) VALUES (?, ?, ?, ?)')
            ->execute([$name, $status, $now, $now]);
    }

    public function markInstalled(string $name): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $this->setStatus($name, 'enabled');
        $this->pdo->prepare('UPDATE modules SET installed_at = :now WHERE name = :name')
            ->execute(['now' => $now, 'name' => $name]);
    }
}










