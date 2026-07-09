<?php

declare(strict_types=1);

use Zoosper\Core\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202607090001_create_auth_tables';
    }

    public function up(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            $engine = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL UNIQUE, name VARCHAR(190) NOT NULL, password_hash VARCHAR(255) NOT NULL, status VARCHAR(32) NOT NULL DEFAULT 'active', last_login_at DATETIME NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX idx_admin_users_status(status))" . $engine);
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_roles (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(100) NOT NULL UNIQUE, label VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)" . $engine);
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_permissions (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(120) NOT NULL UNIQUE, label VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL)" . $engine);
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_user_roles (user_id INT NOT NULL, role_id INT NOT NULL, PRIMARY KEY(user_id, role_id), FOREIGN KEY(user_id) REFERENCES admin_users(id) ON DELETE CASCADE, FOREIGN KEY(role_id) REFERENCES admin_roles(id) ON DELETE CASCADE)" . $engine);
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_role_permissions (role_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(role_id, permission_id), FOREIGN KEY(role_id) REFERENCES admin_roles(id) ON DELETE CASCADE, FOREIGN KEY(permission_id) REFERENCES admin_permissions(id) ON DELETE CASCADE)" . $engine);
            return;
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL UNIQUE, name TEXT NOT NULL, password_hash TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'active', last_login_at TEXT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_users_status ON admin_users(status)');
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL UNIQUE, label TEXT NOT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL UNIQUE, label TEXT NOT NULL, created_at TEXT NOT NULL)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_user_roles (user_id INTEGER NOT NULL, role_id INTEGER NOT NULL, PRIMARY KEY(user_id, role_id), FOREIGN KEY(user_id) REFERENCES admin_users(id) ON DELETE CASCADE, FOREIGN KEY(role_id) REFERENCES admin_roles(id) ON DELETE CASCADE)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_role_permissions (role_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY(role_id, permission_id), FOREIGN KEY(role_id) REFERENCES admin_roles(id) ON DELETE CASCADE, FOREIGN KEY(permission_id) REFERENCES admin_permissions(id) ON DELETE CASCADE)");
    }
};
