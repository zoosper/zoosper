<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\AuditLogGrid;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Grid\GridCriteria;
use Zoosper\Core\Pagination\Pager;

/*
 * Phase A hotfix: REPLACES the Phase 1.112 version of this test file, which
 * referenced the now-deleted AuditLogCriteria class. Exercises the ACTUAL
 * paginate(GridCriteria) method AuditLogRepository implements today.
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

/** Build a bare (unfiltered, page 1) GridCriteria using AuditLogGrid's real definition. */
function auditCriteria(array $overrides = []): GridCriteria
{
    return GridCriteria::fromValues($overrides, AuditLogGrid::definition());
}

it('paginates results with correct totals and page math (no filters)', function (): void {
    $pdo = makeAuditLogPdo();
    seedAuditRows($pdo, 45);
    $repo = new AuditLogRepository($pdo);

    $page1 = $repo->paginate(auditCriteria(['page_size' => '20']));
    expect($page1->items)->toHaveCount(20)
        ->and($page1->total)->toBe(45)
        ->and($page1->totalPages())->toBe(3)
        ->and($page1->hasPrevious())->toBeFalse()
        ->and($page1->hasNext())->toBeTrue();

    $page3 = $repo->paginate(auditCriteria(['page' => '3', 'page_size' => '20']));
    expect($page3->items)->toHaveCount(5)
        ->and($page3->hasNext())->toBeFalse();
});

it('an UNFILTERED, bare criteria (matching a plain page visit) returns all rows', function (): void {
    // This specifically guards the "no records shown on a bare visit" class of
    // regression: GridCriteria::fromValues([], ...) must produce a criteria
    // whose paginate() call returns the full unfiltered result set.
    $pdo = makeAuditLogPdo();
    seedAuditRows($pdo, 7);
    $repo = new AuditLogRepository($pdo);

    $result = $repo->paginate(GridCriteria::fromValues([], AuditLogGrid::definition()));

    expect($result->total)->toBe(7)
        ->and($result->items)->toHaveCount(7);
});

it('filters by entity_type without affecting total across other types', function (): void {
    $pdo = makeAuditLogPdo();
    seedAuditRows($pdo, 10, 'page');
    seedAuditRows($pdo, 5, 'admin_user');
    $repo = new AuditLogRepository($pdo);

    $pages = $repo->paginate(auditCriteria(['entity_type' => 'page', 'page_size' => '50']));
    $users = $repo->paginate(auditCriteria(['entity_type' => 'admin_user', 'page_size' => '50']));

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
    $result = $repo->paginate(auditCriteria(['q' => 'homepage', 'page_size' => '50']));

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
});

it('latest() is unchanged and still returns the raw top-N rows', function (): void {
    $pdo = makeAuditLogPdo();
    seedAuditRows($pdo, 5);
    $repo = new AuditLogRepository($pdo);

    expect($repo->latest(3))->toHaveCount(3);
});

