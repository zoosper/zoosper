<?php

declare(strict_types=1);

use Zoosper\Audit\AuditLogRepository;
use Zoosper\Audit\AuditLogger;

it('redacts sensitive nested metadata before persisting audit actions', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_activity_log (admin_user_id INTEGER, actor_email TEXT, action TEXT, entity_type TEXT, entity_id TEXT, summary TEXT, metadata_json TEXT, ip_address TEXT, user_agent TEXT, created_at TEXT)');
    $logger = new AuditLogger(new AuditLogRepository($pdo));

    $logger->logAction(null, null, 'update', 'user', null, 'Updated', [
        'password' => 'password-value',
        'nested' => ['api_token' => 'token-value', 'name' => 'visible'],
        'object' => (object) ['secret' => 'object-value', 'empty' => (object) []],
    ]);

    $metadata = json_decode((string) $pdo->query('SELECT metadata_json FROM admin_activity_log')->fetchColumn(), true, flags: JSON_THROW_ON_ERROR);
    expect($metadata)->toBe(['password' => '[redacted]', 'nested' => ['api_token' => '[redacted]', 'name' => 'visible'], 'object' => ['secret' => '[redacted]', 'empty' => []]]);
    expect((string) $pdo->query('SELECT metadata_json FROM admin_activity_log')->fetchColumn())->toContain('"empty":{}');
});