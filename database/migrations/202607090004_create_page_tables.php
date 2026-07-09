<?php

declare(strict_types=1);

use PDO;
use Zoosper\Core\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202607090004_create_page_tables';
    }

    public function up(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            $engine = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
            $pdo->exec("CREATE TABLE IF NOT EXISTS pages (id INT AUTO_INCREMENT PRIMARY KEY, site_id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(32) NOT NULL DEFAULT 'draft', meta_title VARCHAR(255) NULL, meta_description TEXT NULL, published_at DATETIME NULL, created_by INT NULL, updated_by INT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE KEY uq_pages_site_slug(site_id, slug), INDEX idx_pages_site_status(site_id, status), FOREIGN KEY(site_id) REFERENCES sites(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES admin_users(id) ON DELETE SET NULL, FOREIGN KEY(updated_by) REFERENCES admin_users(id) ON DELETE SET NULL)" . $engine);
            $pdo->exec("CREATE TABLE IF NOT EXISTS page_revisions (id INT AUTO_INCREMENT PRIMARY KEY, page_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_by INT NULL, created_at DATETIME NOT NULL, FOREIGN KEY(page_id) REFERENCES pages(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES admin_users(id) ON DELETE SET NULL)" . $engine);
            return;
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS pages (id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER NOT NULL, title TEXT NOT NULL, slug TEXT NOT NULL, content TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'draft', meta_title TEXT NULL, meta_description TEXT NULL, published_at TEXT NULL, created_by INTEGER NULL, updated_by INTEGER NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL, UNIQUE(site_id, slug), FOREIGN KEY(site_id) REFERENCES sites(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES admin_users(id) ON DELETE SET NULL, FOREIGN KEY(updated_by) REFERENCES admin_users(id) ON DELETE SET NULL)");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_pages_site_status ON pages(site_id, status)');
        $pdo->exec("CREATE TABLE IF NOT EXISTS page_revisions (id INTEGER PRIMARY KEY AUTOINCREMENT, page_id INTEGER NOT NULL, title TEXT NOT NULL, content TEXT NOT NULL, created_by INTEGER NULL, created_at TEXT NOT NULL, FOREIGN KEY(page_id) REFERENCES pages(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES admin_users(id) ON DELETE SET NULL)");
    }
};
