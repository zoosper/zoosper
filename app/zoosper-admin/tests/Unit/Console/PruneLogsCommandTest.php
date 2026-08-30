<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Console;

use PDO;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Console\PruneLogsCommand;
use Zoosper\Core\Console\ConsoleOutput;

it('prunes audit logs and login history older than given days', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec('
        CREATE TABLE admin_activity_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            admin_user_id INTEGER,
            actor_email TEXT,
            action TEXT,
            entity_type TEXT,
            entity_id TEXT,
            summary TEXT,
            metadata_json TEXT,
            ip_address TEXT,
            user_agent TEXT,
            created_at TEXT
        );
        CREATE TABLE admin_login_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            admin_user_id INTEGER,
            email TEXT,
            status TEXT,
            ip_address TEXT,
            user_agent TEXT,
            created_at TEXT
        );
    ');

    $oldDate = gmdate('Y-m-d H:i:s', time() - (100 * 86400));
    $recentDate = gmdate('Y-m-d H:i:s');

    $pdo->exec("INSERT INTO admin_activity_log (action, summary, created_at) VALUES ('login', 'Old log', '{$oldDate}')");
    $pdo->exec("INSERT INTO admin_activity_log (action, summary, created_at) VALUES ('login', 'New log', '{$recentDate}')");

    $pdo->exec("INSERT INTO admin_login_history (email, status, created_at) VALUES ('user@example.com', 'success', '{$oldDate}')");
    $pdo->exec("INSERT INTO admin_login_history (email, status, created_at) VALUES ('user@example.com', 'success', '{$recentDate}')");

    $auditRepo = new AuditLogRepository($pdo);
    $loginRepo = new LoginHistoryRepository($pdo);
    $command = new PruneLogsCommand($auditRepo, $loginRepo);

    $output = new ConsoleOutput();
    $exitCode = $command->run(['--days=90'], $output);

    expect($exitCode)->toBe(0);

    $auditRemaining = (int) $pdo->query('SELECT COUNT(*) FROM admin_activity_log')->fetchColumn();
    $loginRemaining = (int) $pdo->query('SELECT COUNT(*) FROM admin_login_history')->fetchColumn();

    expect($auditRemaining)->toBe(1)
        ->and($loginRemaining)->toBe(1);
});
