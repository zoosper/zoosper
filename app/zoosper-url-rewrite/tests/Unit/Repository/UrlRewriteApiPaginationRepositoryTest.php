<?php

declare(strict_types=1);

use Zoosper\Pagination\Pager;
use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;

function urlRewritePaginationPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE url_rewrites(id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER NOT NULL,request_path TEXT NOT NULL,target_path TEXT NOT NULL,entity_type TEXT NOT NULL,entity_id INTEGER NULL,redirect_type INTEGER NOT NULL,is_active INTEGER NOT NULL,created_at TEXT NOT NULL,updated_at TEXT NOT NULL)');
    return $pdo;
}

it('returns a bounded Site-scoped URL Rewrite slice with exact metadata', function (): void {
    $repository = new UrlRewriteRepository(urlRewritePaginationPdo());
    for ($i = 1; $i <= 25; $i++) {
        $path = 'path-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        $repository->save(null, 1, $path, '/target-' . $i, 301);
    }
    $repository->save(null, 2, 'other-site', '/other', 301);
    $result = $repository->pageForSite(1, new Pager(2, 10));
    expect($result->total)->toBe(25)
        ->and($result->page)->toBe(2)
        ->and($result->pageSize)->toBe(10)
        ->and($result->totalPages())->toBe(3)
        ->and($result->items)->toHaveCount(10)
        ->and($result->items[0]->requestPath)->toBe('path-11')
        ->and(array_unique(array_map(static fn ($rewrite): int => $rewrite->siteId, $result->items)))->toBe([1]);
});

it('clamps an out-of-range URL Rewrite page to the last Site-scoped page', function (): void {
    $repository = new UrlRewriteRepository(urlRewritePaginationPdo());
    $repository->save(null, 1, 'old', '/new', 301);
    $result = $repository->pageForSite(1, new Pager(99999, 20));
    expect($result->page)->toBe(1)->and($result->items)->toHaveCount(1);
});
