<?php

declare(strict_types=1);

use Zoosper\Site\Repository\SiteRepository;

function makeSitesSqlitePdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec(
        'CREATE TABLE sites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT NOT NULL,
            name TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT "active",
            homepage_slug TEXT,
            theme_code TEXT DEFAULT "default",
            locale TEXT DEFAULT "en_AU",
            currency TEXT DEFAULT "AUD",
            base_url TEXT DEFAULT "",
            website_code TEXT DEFAULT "main",
            store_code TEXT DEFAULT "main",
            store_view_code TEXT DEFAULT "default",
            path_prefix TEXT DEFAULT "",
            created_at TEXT,
            updated_at TEXT
        )'
    );

    $pdo->exec(
        'CREATE TABLE site_domains (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_id INTEGER NOT NULL,
            host TEXT NOT NULL,
            is_primary INTEGER NOT NULL DEFAULT 0,
            created_at TEXT,
            updated_at TEXT
        )'
    );

    return $pdo;
}

it('creates and lists active sites via allActive', function (): void {
    $repo = new SiteRepository(makeSitesSqlitePdo());

    $mainId = $repo->create(code: 'main', name: 'Main Site', host: 'main.test');
    $repo->create(code: 'shop', name: 'Shop Site', host: 'shop.test');

    $active = $repo->allActive();

    expect($active)->toHaveCount(2)
        ->and($active[0])->toBeInstanceOf(\Zoosper\Site\Model\Site::class);

    $codes = array_map(static fn ($s): string => $s->code, $active);
    expect($codes)->toBe(['main', 'shop'])
        ->and($mainId)->toBeGreaterThan(0);
});

it('excludes inactive sites from allActive', function (): void {
    $pdo = makeSitesSqlitePdo();
    $repo = new SiteRepository($pdo);

    $id = $repo->create(code: 'main', name: 'Main', host: 'main.test');
    $pdo->prepare('UPDATE sites SET status = :s WHERE id = :id')
        ->execute(['s' => 'inactive', 'id' => $id]);

    expect($repo->allActive())->toBe([]);
});

it('finds a site by id via findById', function (): void {
    $repo = new SiteRepository(makeSitesSqlitePdo());
    $id = $repo->create(code: 'main', name: 'Main', host: 'main.test', themeCode: 'aurora');

    $site = $repo->findById($id);

    expect($site)->not->toBeNull()
        ->and($site->id)->toBe($id)
        ->and($site->code)->toBe('main')
        ->and($site->themeCode)->toBe('aurora');

    expect($repo->findById(999999))->toBeNull();
});

it('updates a site theme via updateTheme', function (): void {
    $repo = new SiteRepository(makeSitesSqlitePdo());
    $id = $repo->create(code: 'main', name: 'Main', host: 'main.test', themeCode: 'default');

    $repo->updateTheme($id, 'midnight');

    $site = $repo->findById($id);
    expect($site)->not->toBeNull()
        ->and($site->themeCode)->toBe('midnight');
});
