<?php

declare(strict_types=1);

use Zoosper\Admin\PageMomentum\PageMomentumColumnMap;
use Zoosper\Admin\PageMomentum\PageMomentumFactsProvider;
use Zoosper\Admin\PageMomentum\SchemaAdaptivePageMomentumQuery;

/**
 * Build an in-memory SQLite DB with CUSTOM column names to prove the adapter
 * reads through the column map rather than hard-coded names.
 */
function makeCustomSchemaPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec(
        'CREATE TABLE cms_pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            state TEXT,
            live_at TEXT,
            changed_at TEXT
        )'
    );

    $rows = [
        ['Home',      'live', '2026-07-24 10:00:00', '2026-07-24 10:00:00'],
        ['About',     'live', '2026-06-01 09:00:00', '2026-07-20 12:00:00'],
        ['Contact',   'live', '2026-07-19 08:00:00', '2026-07-01 08:00:00'],
        ['Draft One', null,   null,                  '2026-07-22 15:00:00'],
        ['Draft Two', 'wip',  null,                  '2026-05-10 15:00:00'],
    ];
    $stmt = $pdo->prepare('INSERT INTO cms_pages (name, state, live_at, changed_at) VALUES (?, ?, ?, ?)');
    foreach ($rows as $row) {
        $stmt->execute($row);
    }

    return $pdo;
}

it('reads facts through a custom column map', function (): void {
    $now = new DateTimeImmutable('2026-07-25 18:00:00');
    $pdo = makeCustomSchemaPdo();

    $map = PageMomentumColumnMap::fromArray([
        'table' => 'cms_pages',
        'status' => 'state',
        'title' => 'name',
        'published_at' => 'live_at',
        'updated_at' => 'changed_at',
        'published_value' => 'live',
    ]);

    $facts = (new PageMomentumFactsProvider(
        new SchemaAdaptivePageMomentumQuery($pdo, $map, $now)
    ))->facts();

    expect($facts->totalPages)->toBe(5)
        ->and($facts->publishedPages)->toBe(3)
        ->and($facts->draftPages)->toBe(2)
        ->and($facts->publishedLast7Days)->toBe(2)   // Home + Contact
        ->and($facts->updatedLast7Days)->toBe(3)     // Home + About + Draft One
        ->and($facts->mostRecentTitle)->toBe('Home')
        ->and($facts->mostRecentUpdatedAt)->toBe('2026-07-24 10:00:00')
        ->and($facts->publishedShare())->toBe(60);
});

it('degrades gracefully when optional columns are missing', function (): void {
    $now = new DateTimeImmutable('2026-07-25 18:00:00');

    // Table has only id + title; no status/published/updated columns.
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL)');
    $pdo->exec("INSERT INTO pages (title) VALUES ('One'), ('Two')");

    $facts = (new PageMomentumFactsProvider(
        new SchemaAdaptivePageMomentumQuery($pdo, new PageMomentumColumnMap(), $now)
    ))->facts();

    expect($facts->totalPages)->toBe(2)
        ->and($facts->publishedPages)->toBe(0)       // no status column -> 0
        ->and($facts->draftPages)->toBe(2)           // all treated as draft
        ->and($facts->publishedLast7Days)->toBe(0)   // no published_at -> 0
        ->and($facts->updatedLast7Days)->toBe(0)     // no updated_at -> 0
        ->and($facts->mostRecentTitle)->toBeNull()
        ->and($facts->mostRecentUpdatedAt)->toBeNull();
});

it('rejects unsafe column identifiers', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL)');

    $map = PageMomentumColumnMap::fromArray(['status' => 'bad; DROP TABLE pages;--']);

    expect(fn () => new SchemaAdaptivePageMomentumQuery($pdo, $map))
        ->toThrow(InvalidArgumentException::class);
});
