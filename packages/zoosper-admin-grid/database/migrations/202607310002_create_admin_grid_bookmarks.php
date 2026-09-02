<?php

declare(strict_types=1);

return static function (\PDO $pdo, string $driver): void {
    if ($driver === 'mysql') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS admin_grid_bookmarks ('
            . 'id INT AUTO_INCREMENT PRIMARY KEY, '
            . 'admin_user_id INT NOT NULL, grid_key VARCHAR(64) NOT NULL, '
            . 'name VARCHAR(120) NOT NULL, state_json LONGTEXT NOT NULL, '
            . 'is_default TINYINT(1) NOT NULL DEFAULT 0, '
            . 'created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, '
            . 'UNIQUE INDEX idx_admin_grid_bookmarks_user_grid_name '
            . '(admin_user_id, grid_key, name), '
            . 'INDEX idx_admin_grid_bookmarks_default '
            . '(admin_user_id, grid_key, is_default), '
            . 'CONSTRAINT fk_admin_grid_bookmarks_user FOREIGN KEY (admin_user_id) '
            . 'REFERENCES admin_users (id) ON DELETE CASCADE ON UPDATE RESTRICT'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        );
        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS admin_grid_bookmarks ('
        . 'id INTEGER PRIMARY KEY AUTOINCREMENT, '
        . 'admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, '
        . 'name TEXT NOT NULL, state_json TEXT NOT NULL, '
        . 'is_default INTEGER NOT NULL DEFAULT 0, '
        . 'created_at TEXT NOT NULL, updated_at TEXT NOT NULL, '
        . 'CONSTRAINT fk_admin_grid_bookmarks_user FOREIGN KEY (admin_user_id) '
        . 'REFERENCES admin_users (id) ON DELETE CASCADE ON UPDATE RESTRICT'
        . ')',
    );
    $pdo->exec(
        'CREATE UNIQUE INDEX IF NOT EXISTS idx_admin_grid_bookmarks_user_grid_name '
        . 'ON admin_grid_bookmarks(admin_user_id, grid_key, name)',
    );
    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS idx_admin_grid_bookmarks_default '
        . 'ON admin_grid_bookmarks(admin_user_id, grid_key, is_default)',
    );
};











