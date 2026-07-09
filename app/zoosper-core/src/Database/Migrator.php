<?php

declare(strict_types=1);

namespace Zoosper\Core\Database;

use PDO;
use RuntimeException;

final readonly class Migrator
{
    public function __construct(
        private PDO $pdo,
        private string $path,
    ) {
    }

    public function migrate(): void
    {
        $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->ensureMigrationsTable($driver);
        $ran = $this->ranMigrations();

        foreach ($this->migrationFiles() as $file) {
            $migration = require $file;

            if (!$migration instanceof MigrationInterface) {
                throw new RuntimeException('Bad migration file: ' . $file);
            }

            if (in_array($migration->name(), $ran, true)) {
                continue;
            }

            $migration->up($this->pdo, $driver);

            $statement = $this->pdo->prepare(
                'INSERT INTO migrations (migration, migrated_at) VALUES (:migration, :migrated_at)'
            );
            $statement->execute([
                'migration' => $migration->name(),
                'migrated_at' => gmdate('Y-m-d H:i:s'),
            ]);
        }
    }

    private function ensureMigrationsTable(string $driver): void
    {
        if ($driver === 'mysql') {
            $this->pdo->exec(
                'CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    migrated_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            return;
        }

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL UNIQUE,
                migrated_at TEXT NOT NULL
            )'
        );
    }

    /**
     * @return list<string>
     */
    private function ranMigrations(): array
    {
        $rows = $this->pdo
            ->query('SELECT migration FROM migrations')
            ->fetchAll();

        return array_map(
            static fn (array $row): string => (string) $row['migration'],
            $rows,
        );
    }

    /**
     * @return list<string>
     */
    private function migrationFiles(): array
    {
        $files = glob($this->path . '/*.php') ?: [];
        sort($files);

        return $files;
    }
}
