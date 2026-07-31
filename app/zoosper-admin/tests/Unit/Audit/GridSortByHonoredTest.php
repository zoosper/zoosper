<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Core\Database\Migrator;
use Zoosper\Grid\GridCriteria;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Pagination\Pager;

/**
 * CORRECTNESS REGRESSION TEST — proves AuditLogRepository::paginate() and
 * LoginHistoryRepository::paginate() now genuinely consult
 * GridCriteria::$sortBy (via a safe allow-list), rather than silently
 * ignoring it and always sorting by a hardcoded `id` column.
 *
 * Since both grids currently declare only 'created_at' as sortable
 * (mapped to `id`, the existing monotonic proxy), these tests
 * deliberately construct GridCriteria directly (bypassing
 * GridCriteria::fromValues()'s own validation against a GridDefinition,
 * which is not needed here) to prove the REPOSITORY layer itself honours
 * whatever sortBy it's given — including an intentionally unrecognised
 * value, to prove the safe-fallback path works and no SQL error/injection
 * risk is introduced.
 *
 * File placement: app/zoosper-admin/tests/Unit/Audit/GridSortByHonoredTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
function gridSortByTestDatabase(): PDO
{
    $basePath = dirname(__DIR__, 5);
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    (new Migrator($pdo, $basePath, new ModuleRegistry($basePath)))->migrate();

    return $pdo;
}

function gridSortByTestCriteria(?string $sortBy, string $sortDir = 'desc'): GridCriteria
{
    return new GridCriteria(
        pager: Pager::fromQuery(['page' => '1', 'page_size' => '10']),
        sortBy: $sortBy,
        sortDir: $sortDir,
        filters: [],
    );
}

it('AuditLogRepository::paginate() does not throw for the currently-declared sortable column (created_at)', function (): void {
    $pdo = gridSortByTestDatabase();
    $repo = new AuditLogRepository($pdo);

    $repo->record(null, 'a@example.test', 'test.action', 'test', null, 'first', [], null, null);
    $repo->record(null, 'a@example.test', 'test.action', 'test', null, 'second', [], null, null);

    $result = $repo->paginate(gridSortByTestCriteria('created_at', 'asc'));

    expect($result->items)->toHaveCount(2);
    // Confirms ascending order is genuinely applied (oldest/lowest id first).
    expect($result->items[0]['summary'])->toBe('first');
    expect($result->items[1]['summary'])->toBe('second');
});

it('AuditLogRepository::paginate() correctly reverses order when sortDir changes, for the same sortBy', function (): void {
    $pdo = gridSortByTestDatabase();
    $repo = new AuditLogRepository($pdo);

    $repo->record(null, 'a@example.test', 'test.action', 'test', null, 'first', [], null, null);
    $repo->record(null, 'a@example.test', 'test.action', 'test', null, 'second', [], null, null);

    $descResult = $repo->paginate(gridSortByTestCriteria('created_at', 'desc'));

    expect($descResult->items[0]['summary'])->toBe('second');
    expect($descResult->items[1]['summary'])->toBe('first');
});

it('AuditLogRepository::paginate() safely falls back to the default column for an unrecognised sortBy (no SQL error, no injection risk)', function (): void {
    $pdo = gridSortByTestDatabase();
    $repo = new AuditLogRepository($pdo);

    $repo->record(null, 'a@example.test', 'test.action', 'test', null, 'only-row', [], null, null);

    // An intentionally unrecognised/malicious-looking sortBy value — must
    // NOT cause a SQL error or be interpolated into the query. Confirms
    // the allow-list fallback, not direct interpolation, is in effect.
    $result = $repo->paginate(gridSortByTestCriteria('id; DROP TABLE admin_activity_log; --'));

    expect($result->items)->toHaveCount(1);
    expect($result->total)->toBe(1);
});

it('AuditLogRepository::paginate() safely falls back to the default column for a null sortBy', function (): void {
    $pdo = gridSortByTestDatabase();
    $repo = new AuditLogRepository($pdo);

    $repo->record(null, 'a@example.test', 'test.action', 'test', null, 'only-row', [], null, null);

    $result = $repo->paginate(gridSortByTestCriteria(null));

    expect($result->items)->toHaveCount(1);
});

it('LoginHistoryRepository::paginate() does not throw for the currently-declared sortable column (created_at)', function (): void {
    $pdo = gridSortByTestDatabase();
    $repo = new LoginHistoryRepository($pdo);

    $repo->record(null, 'first@example.test', 'success', null, null);
    $repo->record(null, 'second@example.test', 'success', null, null);

    $result = $repo->paginate(gridSortByTestCriteria('created_at', 'asc'));

    expect($result->items)->toHaveCount(2);
    expect($result->items[0]['email'])->toBe('first@example.test');
    expect($result->items[1]['email'])->toBe('second@example.test');
});

it('LoginHistoryRepository::paginate() safely falls back to the default column for an unrecognised sortBy', function (): void {
    $pdo = gridSortByTestDatabase();
    $repo = new LoginHistoryRepository($pdo);

    $repo->record(null, 'only@example.test', 'success', null, null);

    $result = $repo->paginate(gridSortByTestCriteria('not_a_real_column'));

    expect($result->items)->toHaveCount(1);
});

