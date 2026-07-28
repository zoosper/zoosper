<?php

declare(strict_types=1);

use Zoosper\Core\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202607090006_create_audit_login_history';
    }

    public function up(\PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            $engine = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_login_history (id INT AUTO_INCREMENT PRIMARY KEY, admin_user_id INT NULL, email VARCHAR(190) NOT NULL, status VARCHAR(32) NOT NULL, ip_address VARCHAR(64) NULL, user_agent TEXT NULL, created_at DATETIME NOT NULL, INDEX idx_admin_login_history_user(admin_user_id), INDEX idx_admin_login_history_email(email), INDEX idx_admin_login_history_status(status), INDEX idx_admin_login_history_created(created_at), FOREIGN KEY(admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL)" . $engine);
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_activity_log (id INT AUTO_INCREMENT PRIMARY KEY, admin_user_id INT NULL, actor_email VARCHAR(190) NULL, action VARCHAR(120) NOT NULL, entity_type VARCHAR(120) NOT NULL, entity_id VARCHAR(120) NULL, summary TEXT NOT NULL, metadata_json LONGTEXT NULL, ip_address VARCHAR(64) NULL, user_agent TEXT NULL, created_at DATETIME NOT NULL, INDEX idx_admin_activity_user(admin_user_id), INDEX idx_admin_activity_action(action), INDEX idx_admin_activity_entity(entity_type, entity_id), INDEX idx_admin_activity_created(created_at), FOREIGN KEY(admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL)" . $engine);
            return;
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_login_history (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NULL, email TEXT NOT NULL, status TEXT NOT NULL, ip_address TEXT NULL, user_agent TEXT NULL, created_at TEXT NOT NULL, FOREIGN KEY(admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL)");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_login_history_user ON admin_login_history(admin_user_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_login_history_email ON admin_login_history(email)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_login_history_status ON admin_login_history(status)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_login_history_created ON admin_login_history(created_at)');
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_activity_log (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NULL, actor_email TEXT NULL, action TEXT NOT NULL, entity_type TEXT NOT NULL, entity_id TEXT NULL, summary TEXT NOT NULL, metadata_json TEXT NULL, ip_address TEXT NULL, user_agent TEXT NULL, created_at TEXT NOT NULL, FOREIGN KEY(admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL)");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_activity_user ON admin_activity_log(admin_user_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_activity_action ON admin_activity_log(action)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_activity_entity ON admin_activity_log(entity_type, entity_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_activity_created ON admin_activity_log(created_at)');
    }
};
