<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\LoginHistoryGrid;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Core\Grid\GridCriteria;

/*
 * Phase A hotfix: REPLACES the Phase 1.112 version of this test file, which
 * referenced the now-deleted LoginHistoryCriteria class. Exercises the ACTUAL
 * paginate(GridCriteria) method LoginHistoryRepository implements today.
 */

function makeLoginHistoryPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE admin_login_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            admin_user_id INTEGER,
            email TEXT NOT NULL,
            status TEXT NOT NULL,
            ip_address TEXT,
            user_agent TEXT,
            created_at TEXT NOT NULL
        )'
    );
    return $pdo;
}

function seedLoginRows(PDO $pdo, int $count, string $status = 'success'): void
{
    $stmt = $pdo->prepare('INSERT INTO admin_login_history (email, status, created_at) VALUES (?,?,?)');
    for ($i = 1; $i <= $count; $i++) {
        $stmt->execute(['user' . $i . '@example.test', $status, sprintf('2026-07-%02d 00:00:00', min(28, $i))]);
    }
}

function loginCriteria(array $overrides = []): GridCriteria
{
    return GridCriteria::fromValues($overrides, LoginHistoryGrid::definition());
}

it('paginates login history with correct totals and page math (no filters)', function (): void {
    $pdo = makeLoginHistoryPdo();
    seedLoginRows($pdo, 33);
    $repo = new LoginHistoryRepository($pdo);

    $page1 = $repo->paginate(loginCriteria(['page_size' => '20']));
    expect($page1->items)->toHaveCount(20)
        ->and($page1->total)->toBe(33)
        ->and($page1->totalPages())->toBe(2);

    $page2 = $repo->paginate(loginCriteria(['page' => '2', 'page_size' => '20']));
    expect($page2->items)->toHaveCount(13);
});

it('an UNFILTERED, bare criteria (matching a plain page visit) returns all rows', function (): void {
    $pdo = makeLoginHistoryPdo();
    seedLoginRows($pdo, 9);
    $repo = new LoginHistoryRepository($pdo);

    $result = $repo->paginate(GridCriteria::fromValues([], LoginHistoryGrid::definition()));

    expect($result->total)->toBe(9)
        ->and($result->items)->toHaveCount(9);
});

it('filters by status', function (): void {
    $pdo = makeLoginHistoryPdo();
    seedLoginRows($pdo, 6, 'success');
    seedLoginRows($pdo, 4, 'otp_failed');
    $repo = new LoginHistoryRepository($pdo);

    $failures = $repo->paginate(loginCriteria(['status' => 'otp_failed', 'page_size' => '50']));
    expect($failures->total)->toBe(4);
});

it('deleteOlderThan removes only rows before the cutoff', function (): void {
    $pdo = makeLoginHistoryPdo();
    $stmt = $pdo->prepare('INSERT INTO admin_login_history (email, status, created_at) VALUES (?,?,?)');
    $stmt->execute(['old@example.test', 'success', '2025-12-01 00:00:00']);
    $stmt->execute(['recent@example.test', 'success', '2026-07-20 00:00:00']);

    $repo = new LoginHistoryRepository($pdo);
    $deleted = $repo->deleteOlderThan('2026-01-01 00:00:00');

    expect($deleted)->toBe(1);
    $remainingEmail = $pdo->query('SELECT email FROM admin_login_history')->fetchColumn();
    expect($remainingEmail)->toBe('recent@example.test');
});
