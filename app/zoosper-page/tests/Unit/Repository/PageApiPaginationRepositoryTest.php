<?php

declare(strict_types=1);

use Zoosper\Page\Repository\PageRepository;
use Zoosper\Pagination\Pager;

it('returns a bounded Site-scoped Page slice with exact pagination metadata', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER NOT NULL, title TEXT NOT NULL, slug TEXT NOT NULL, content TEXT NOT NULL, status TEXT NOT NULL, created_by INTEGER, updated_by INTEGER, created_at TEXT, updated_at TEXT, published_at TEXT)');
    for ($i = 1; $i <= 25; $i++) {
        $site = $i === 25 ? 2 : 1;
        $pdo->exec("INSERT INTO pages (site_id,title,slug,content,status,created_at,updated_at) VALUES ($site,'Page $i','page-$i','body','draft','2026-01-01','2026-01-01')");
    }

    $result = (new PageRepository($pdo))->pageForSite(1, new Pager(2, 10));

    expect($result->total)->toBe(24)
        ->and($result->page)->toBe(2)
        ->and($result->pageSize)->toBe(10)
        ->and($result->totalPages())->toBe(3)
        ->and($result->items)->toHaveCount(10)
        ->and($result->items[0]->siteId)->toBe(1)
        ->and($result->items[0]->id)->toBe(14);
});

it('clamps an out-of-range Page collection request to the last available page', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER NOT NULL, title TEXT NOT NULL, slug TEXT NOT NULL, content TEXT NOT NULL, status TEXT NOT NULL, created_by INTEGER, updated_by INTEGER, created_at TEXT, updated_at TEXT, published_at TEXT)');
    $pdo->exec("INSERT INTO pages (site_id,title,slug,content,status,created_at,updated_at) VALUES (1,'Only','only','body','draft','2026-01-01','2026-01-01')");

    $result = (new PageRepository($pdo))->pageForSite(1, new Pager(99999, 20));

    expect($result->page)->toBe(1)->and($result->items)->toHaveCount(1);
});
