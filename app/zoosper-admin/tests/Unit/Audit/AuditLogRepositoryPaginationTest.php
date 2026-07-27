<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\AuditLogCriteria;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Core\Pagination\Pager;

/*
 * Phase 1.112 behavioural tests for AuditLogRepository pagination + retention
 * (Sonnet Phase 2 §4.2). Uses a real in-memory SQLite table.
 */

function makeAuditLogPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE admin_activity_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            admin_user_id INTEGER,
            actor_email TEXT,
            action TEXT NOT NULL,
            entity_type TEXT NOT NULL,
            entity_id TEXT,
            summary TEXT NOT NULL,
            metadata_json TEXT,
            ip_address TEXT,
            user_agent TEXT,
            created_at TEXT NOT NULL
        )'
    );
    return $pdo;
}

function seedAuditRows(PDO $pdo, int $count, string $entityType = 'page'): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO admin_activity_log (actor_email, action, entity_type, entity_id, summary, metadata_json, created_at)
         VALUES (:email, :action, :entity_type, :entity_id, :summary, :metadata, :created_at)'
    );
    for ($i = 1; $i <= $count; $i++) {
        $stmt->execute([
            'email' => 'actor@example.test',
            'action' => 'updated',
            'entity_type' => $entityType,
            'entity_id' => (string) $i,
            'summary' => 'Row ' . $i,
            'metadata' => '{}',
            'created_at' => sprintf('2026-07-%02d 00:00:00', min(28, $i)),
        ]);
    }
}

it('paginates results with correct totals and page math', function (): void {
    $pdo = makeAuditLogPdo();
    seedAuditRows($pdo, 45);
    $repo = new AuditLogRepository($pdo);

    $page1 = $repo->paginate(new AuditLogCriteria(new Pager(1, 20)));
    expect($page1->items)->toHaveCount(20)
        ->and($page1->total)->toBe(45)
        ->and($page1->totalPages())->toBe(3)
        ->and($page1->hasPrevious())->toBeFalse()
        ->and($page1->hasNext())->toBeTrue();

    $page3 = $repo->paginate(new AuditLogCriteria(new Pager(3, 20)));
    expect($page3->items)->toHaveCount(5) // 45 - 40
        ->and($page3->hasNext())->toBeFalse()
        ->and($page3->hasPrevious())->toBeTrue();
});

it('filters by entity_type without affecting total across other types', function (): void {
    $pdo = makeAuditLogPdo();
    seedAuditRows($pdo, 10, 'page');
    seedAuditRows($pdo, 5, 'admin_user');
    $repo = new AuditLogRepository($pdo);

    $pages = $repo->paginate(new AuditLogCriteria(new Pager(1, 50), entityType: 'page'));
    $users = $repo->paginate(new AuditLogCriteria(new Pager(1, 50), entityType: 'admin_user'));

    expect($pages->total)->toBe(10)
        ->and($users->total)->toBe(5);
});

it('filters by free-text query across summary/action/actor_email', function (): void {
    $pdo = makeAuditLogPdo();
    $pdo->prepare('INSERT INTO admin_activity_log (actor_email, action, entity_type, summary, created_at) VALUES (?,?,?,?,?)')
        ->execute(['alice@example.test', 'created', 'page', 'Created homepage', '2026-07-01 00:00:00']);
    $pdo->prepare('INSERT INTO admin_activity_log (actor_email, action, entity_type, summary, created_at) VALUES (?,?,?,?,?)')
        ->execute(['bob@example.test', 'deleted', 'page', 'Deleted about page', '2026-07-02 00:00:00']);

    $repo = new AuditLogRepository($pdo);

    $result = $repo->paginate(new AuditLogCriteria(new Pager(1, 50), query: 'homepage'));

    expect($result->total)->toBe(1)
        ->and($result->items[0]['actor_email'])->toBe('alice@example.test');
});

it('deleteOlderThan removes only rows before the cutoff', function (): void {
    $pdo = makeAuditLogPdo();
    $stmt = $pdo->prepare('INSERT INTO admin_activity_log (action, entity_type, summary, created_at) VALUES (?,?,?,?)');
    $stmt->execute(['created', 'page', 'old row', '2026-01-01 00:00:00']);
    $stmt->execute(['created', 'page', 'recent row', '2026-07-20 00:00:00']);

    $repo = new AuditLogRepository($pdo);
    $deleted = $repo->deleteOlderThan('2026-06-01 00:00:00');

    expect($deleted)->toBe(1);

    $remaining = (int) $pdo->query('SELECT COUNT(*) FROM admin_activity_log')->fetchColumn();
    expect($remaining)->toBe(1);

    $summary = $pdo->query('SELECT summary FROM admin_activity_log')->fetchColumn();
    expect($summary)->toBe('recent row');
});

it('latest() is unchanged and still returns the raw top-N rows', function (): void {
    $pdo = makeAuditLogPdo();
    seedAuditRows($pdo, 5);
    $repo = new AuditLogRepository($pdo);

    $rows = $repo->latest(3);

    expect($rows)->toHaveCount(3);
});
