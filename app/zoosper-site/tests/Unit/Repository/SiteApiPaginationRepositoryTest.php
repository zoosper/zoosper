<?php

declare(strict_types=1);

use Zoosper\Pagination\Pager;
use Zoosper\Site\Repository\SiteRepository;

it('returns a bounded Site slice with exact pagination metadata', function (): void {
    $pdo = makeSitesSqlitePdo();
    $repository = new SiteRepository($pdo);
    for ($i = 1; $i <= 25; $i++) {
        $label = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        $repository->create(code: 'site-' . $i, name: 'Site ' . $label, host: 'site-' . $i . '.test');
    }
    $result = $repository->pageForApi(new Pager(2, 10));
    expect($result->total)->toBe(25)
        ->and($result->page)->toBe(2)
        ->and($result->pageSize)->toBe(10)
        ->and($result->totalPages())->toBe(3)
        ->and($result->items)->toHaveCount(10)
        ->and($result->items[0]->name)->toBe('Site 11');
});

it('clamps an out-of-range Site page to the last available page', function (): void {
    $repository = new SiteRepository(makeSitesSqlitePdo());
    $repository->create(code: 'main', name: 'Main', host: 'main.test');
    $result = $repository->pageForApi(new Pager(99999, 20));
    expect($result->page)->toBe(1)->and($result->items)->toHaveCount(1);
});
