<?php

declare(strict_types=1);

use Zoosper\Audit\AuditLogRepository;
use Zoosper\Grid\GridCriteria;
use Zoosper\Pagination\Pager;

function auditSearchDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

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
        )',
    );

    return $pdo;
}

it('searches Audit Log summary, action and actor with distinct PDO placeholders', function (): void {
    $pdo = auditSearchDatabase();

    $pdo->exec(
        "INSERT INTO admin_activity_log
            (actor_email, action, entity_type, entity_id, summary, created_at)
         VALUES
            (
                'admin@example.test',
                'page.updated',
                'page',
                '4',
                'Updated About page',
                '2026-09-02 00:00:00'
            ),
            (
                'editor@example.test',
                'site.created',
                'site',
                '2',
                'Created Main Website',
                '2026-09-02 00:01:00'
            )",
    );

    $result = (new AuditLogRepository($pdo))->paginate(
        new GridCriteria(
            pager: new Pager(1, 20),
            filters: ['q' => 'about'],
            sortBy: 'created_at',
            sortDir: 'desc',
        ),
    );

    expect($result->total)->toBe(1)
        ->and($result->items)->toHaveCount(1)
        ->and($result->items[0]['action'])->toBe('page.updated')
        ->and($result->items[0]['actor_email'])->toBe('admin@example.test');
});

it('searches Audit Log actor email through the same canonical q filter', function (): void {
    $pdo = auditSearchDatabase();

    $pdo->exec(
        "INSERT INTO admin_activity_log
            (actor_email, action, entity_type, entity_id, summary, created_at)
         VALUES
            (
                'admin@example.test',
                'page.updated',
                'page',
                '4',
                'Updated a page',
                '2026-09-02 00:00:00'
            )",
    );

    $result = (new AuditLogRepository($pdo))->paginate(
        new GridCriteria(
            pager: new Pager(1, 20),
            filters: ['q' => 'admin@example'],
            sortBy: 'created_at',
            sortDir: 'desc',
        ),
    );

    expect($result->total)->toBe(1)
        ->and($result->items)->toHaveCount(1);
});

it('does not reuse named Audit Log search placeholders', function (): void {
    $source = (string) file_get_contents(
        dirname(__DIR__, 2) . '/src/AuditLogRepository.php',
    );

    expect($source)
        ->toContain('summary LIKE :query_summary')
        ->toContain('action LIKE :query_action')
        ->toContain('actor_email LIKE :query_actor')
        ->toContain("\$params['query_summary'] = \$search;")
        ->toContain("\$params['query_action'] = \$search;")
        ->toContain("\$params['query_actor'] = \$search;")
        ->not->toContain('summary LIKE :query OR')
        ->not->toContain('action LIKE :query OR');
});
