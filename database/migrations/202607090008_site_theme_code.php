<?php

declare(strict_types=1);

use Zoosper\Core\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202607090008_site_theme_code';
    }

    public function up(\PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
            $statement->execute(['table' => 'sites', 'column' => 'theme_code']);
            if ((int) $statement->fetchColumn() === 0) {
                $pdo->exec("ALTER TABLE sites ADD COLUMN theme_code VARCHAR(120) NOT NULL DEFAULT 'default'");
            }
            return;
        }

        $columns = $pdo->query('PRAGMA table_info(sites)')->fetchAll();
        $names = array_map(static fn (array $row): string => (string) $row['name'], $columns);
        if (!in_array('theme_code', $names, true)) {
            $pdo->exec("ALTER TABLE sites ADD COLUMN theme_code TEXT NOT NULL DEFAULT 'default'");
        }
    }
};
