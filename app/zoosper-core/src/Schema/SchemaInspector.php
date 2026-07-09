<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

use PDO;

final readonly class SchemaInspector
{
    public function __construct(private PDO $pdo, private string $driver)
    {
    }

    public function tableExists(string $table): bool
    {
        if ($this->driver === 'mysql') {
            $statement = $this->pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
            $statement->execute(['table' => $table]);
            return (int) $statement->fetchColumn() > 0;
        }

        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = :table");
        $statement->execute(['table' => $table]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function columnExists(string $table, string $column): bool
    {
        if ($this->driver === 'mysql') {
            $statement = $this->pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
            $statement->execute(['table' => $table, 'column' => $column]);
            return (int) $statement->fetchColumn() > 0;
        }

        $statement = $this->pdo->query('PRAGMA table_info(' . $table . ')');
        foreach ($statement->fetchAll() as $row) {
            if ((string) $row['name'] === $column) {
                return true;
            }
        }
        return false;
    }

    public function indexExists(string $table, string $index): bool
    {
        if ($this->driver === 'mysql') {
            $statement = $this->pdo->prepare('SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND INDEX_NAME = :index_name');
            $statement->execute(['table' => $table, 'index_name' => $index]);
            return (int) $statement->fetchColumn() > 0;
        }

        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'index' AND name = :index_name");
        $statement->execute(['index_name' => $index]);
        return (int) $statement->fetchColumn() > 0;
    }
}
