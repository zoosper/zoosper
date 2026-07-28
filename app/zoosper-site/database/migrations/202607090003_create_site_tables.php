<?php

declare(strict_types=1);

use Zoosper\Core\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202607090003_create_site_tables';
    }

    public function up(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            $engine = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
            $pdo->exec("CREATE TABLE IF NOT EXISTS sites (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(100) NOT NULL UNIQUE, name VARCHAR(190) NOT NULL, status VARCHAR(32) NOT NULL DEFAULT 'active', homepage_slug VARCHAR(190) NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX idx_sites_status(status))" . $engine);
            $pdo->exec("CREATE TABLE IF NOT EXISTS site_domains (id INT AUTO_INCREMENT PRIMARY KEY, site_id INT NOT NULL, host VARCHAR(190) NOT NULL UNIQUE, is_primary TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY(site_id) REFERENCES sites(id) ON DELETE CASCADE)" . $engine);
            return;
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS sites (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL UNIQUE, name TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'active', homepage_slug TEXT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_sites_status ON sites(status)');
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_domains (id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER NOT NULL, host TEXT NOT NULL UNIQUE, is_primary INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL, updated_at TEXT NOT NULL, FOREIGN KEY(site_id) REFERENCES sites(id) ON DELETE CASCADE)");
    }
};
