<?php

declare(strict_types=1);

use Zoosper\Admin\PageMomentum\PageMomentumFactsProvider;
use Zoosper\Admin\PageMomentum\SqlitePageMomentumQuery;

/**
 * Build an in-memory SQLite database seeded with a deterministic `pages`
 * fixture, so the facts logic is verified without a live database.
 */
function makePageMomentumPdo(string $now): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec(
        'CREATE TABLE pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            status TEXT,
            published_at TEXT,
            updated_at TEXT
        )'
    );

    // now = 2026-07-25. Recent window = 7 days => threshold 2026-07-18.
    $rows = [
        // published, recent publish + recent update, newest update
        ['Home',        'published', '2026-07-24 10:00:00', '2026-07-24 10:00:00'],
        // published, old publish, recent update
        ['About',       'published', '2026-06-01 09:00:00', '2026-07-20 12:00:00'],
        // published, recent publish, old update
        ['Contact',     'published', '2026-07-19 08:00:00', '2026-07-01 08:00:00'],
        // draft (null status), recent update
        ['Draft One',   null,        null,                  '2026-07-22 15:00:00'],
        // draft (explicit), old update
        ['Draft Two',   'draft',     null,                  '2026-05-10 15:00:00'],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO pages (title, status, published_at, updated_at) VALUES (?, ?, ?, ?)'
    );
    foreach ($rows as $row) {
        $stmt->execute($row);
    }

    return $pdo;
}

it('computes page momentum facts from a real sqlite fixture', function (): void {
    $now = new DateTimeImmutable('2026-07-25 18:00:00');
    $pdo = makePageMomentumPdo($now->format('Y-m-d H:i:s'));

    $query = new SqlitePageMomentumQuery($pdo, 'pages', $now);
    $facts = (new PageMomentumFactsProvider($query))->facts();

    expect($facts->totalPages)->toBe(5)
        ->and($facts->publishedPages)->toBe(3)
        ->and($facts->draftPages)->toBe(2)
        // published within last 7 days: Home (24th) + Contact (19th) = 2
        ->and($facts->publishedLast7Days)->toBe(2)
        // updated within last 7 days: Home (24th) + About (20th) + Draft One (22nd) = 3
        ->and($facts->updatedLast7Days)->toBe(3)
        // most recently updated overall is Home on the 24th
        ->and($facts->mostRecentTitle)->toBe('Home')
        ->and($facts->mostRecentUpdatedAt)->toBe('2026-07-24 10:00:00');
});

it('derives a published share percentage', function (): void {
    $now = new DateTimeImmutable('2026-07-25 18:00:00');
    $pdo = makePageMomentumPdo($now->format('Y-m-d H:i:s'));

    $facts = (new PageMomentumFactsProvider(
        new SqlitePageMomentumQuery($pdo, 'pages', $now)
    ))->facts();

    // 3 of 5 published => 60%
    expect($facts->publishedShare())->toBe(60);

    $array = $facts->toArray();
    expect($array)->toHaveKey('published_share_percent')
        ->and($array['published_share_percent'])->toBe(60)
        ->and($array['total_pages'])->toBe(5);
});

it('handles an empty pages table safely', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            status TEXT,
            published_at TEXT,
            updated_at TEXT
        )'
    );

    $facts = (new PageMomentumFactsProvider(
        new SqlitePageMomentumQuery($pdo, 'pages', new DateTimeImmutable('2026-07-25 18:00:00'))
    ))->facts();

    expect($facts->totalPages)->toBe(0)
        ->and($facts->publishedShare())->toBe(0)
        ->and($facts->mostRecentTitle)->toBeNull()
        ->and($facts->mostRecentUpdatedAt)->toBeNull();
});
