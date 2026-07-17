<?php

declare(strict_types=1);

namespace Zoosper\Site\Tests\Unit\Repository;

use PDO;
use Zoosper\Site\Repository\SiteRepository;

/** In-memory SQLite with the enriched sites + site_domains tables. */
function makeEnrichedSitesPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec(
        'CREATE TABLE sites ('
        . ' id INTEGER PRIMARY KEY AUTOINCREMENT,'
        . ' code TEXT NOT NULL UNIQUE,'
        . ' name TEXT NOT NULL,'
        . " status TEXT NOT NULL DEFAULT 'active',"
        . ' homepage_slug TEXT NULL,'
        . " theme_code TEXT NOT NULL DEFAULT 'default',"
        . " locale TEXT NOT NULL DEFAULT 'en_AU',"
        . " currency TEXT NOT NULL DEFAULT 'AUD',"
        . " base_url TEXT NOT NULL DEFAULT '',"
        . " website_code TEXT NOT NULL DEFAULT 'main',"
        . " store_code TEXT NOT NULL DEFAULT 'main',"
        . " store_view_code TEXT NOT NULL DEFAULT 'default',"
        . " path_prefix TEXT NOT NULL DEFAULT '',"
        . ' created_at TEXT NOT NULL,'
        . ' updated_at TEXT NOT NULL'
        . ')'
    );

    $pdo->exec(
        'CREATE TABLE site_domains ('
        . ' id INTEGER PRIMARY KEY AUTOINCREMENT,'
        . ' site_id INTEGER NOT NULL,'
        . ' host TEXT NOT NULL UNIQUE,'
        . ' is_primary INTEGER NOT NULL DEFAULT 0,'
        . ' created_at TEXT NOT NULL,'
        . ' updated_at TEXT NOT NULL'
        . ')'
    );

    return $pdo;
}

test('create persists and hydrates the enriched store-view dimensions', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $repository = new SiteRepository(makeEnrichedSitesPdo());
    $id = $repository->create(
        code: 'nz',
        name: 'NZ Store',
        host: 'nz.example',
        homepageSlug: 'home',
        themeCode: 'aurora',
        locale: 'en_NZ',
        currency: 'NZD',
        baseUrl: 'https://nz.example',
        websiteCode: 'anz',
        storeCode: 'nz',
        storeViewCode: 'nz_en',
        pathPrefix: '/nz',
    );

    $site = $repository->findById($id);

    expect($site)->not->toBeNull();
    expect($site->locale)->toBe('en_NZ');
    expect($site->currency)->toBe('NZD');
    expect($site->baseUrl)->toBe('https://nz.example');
    expect($site->websiteCode)->toBe('anz');
    expect($site->storeCode)->toBe('nz');
    expect($site->storeViewCode)->toBe('nz_en');
    expect($site->pathPrefix)->toBe('/nz');
    expect($site->themeCode)->toBe('aurora');
});

test('hydration falls back to defaults when enriched columns are absent', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec(
        'CREATE TABLE sites ('
        . ' id INTEGER PRIMARY KEY AUTOINCREMENT,'
        . ' code TEXT NOT NULL UNIQUE,'
        . ' name TEXT NOT NULL,'
        . " status TEXT NOT NULL DEFAULT 'active',"
        . ' homepage_slug TEXT NULL,'
        . ' created_at TEXT NOT NULL,'
        . ' updated_at TEXT NOT NULL'
        . ')'
    );
    $now = gmdate('Y-m-d H:i:s');
    $pdo->prepare('INSERT INTO sites (code, name, status, homepage_slug, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute(['legacy', 'Legacy Site', 'active', 'home', $now, $now]);

    $site = (new SiteRepository($pdo))->findByCode('legacy');

    expect($site)->not->toBeNull();
    expect($site->locale)->toBe('en_AU');
    expect($site->currency)->toBe('AUD');
    expect($site->baseUrl)->toBe('');
    expect($site->websiteCode)->toBe('main');
    expect($site->storeViewCode)->toBe('default');
    expect($site->themeCode)->toBe('default');
});