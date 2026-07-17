<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Site;

use PDO;
use Zoosper\Core\Site\SiteContextResolver;
use Zoosper\Site\Repository\SiteRepository;

function makeSiteIdResolverPdo(): PDO
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

/** @return array<string, mixed> */
function siteIdFallbackConfig(): array
{
    return [
        'default_store_view' => 'default',
        'store_views' => [
            'default' => [
                'website_code' => 'config',
                'website_name' => 'Config Site',
                'store_code' => 'config',
                'store_name' => 'Config Store',
                'store_view_code' => 'default',
                'store_view_name' => 'Default',
                'locale' => 'en_AU',
                'currency' => 'AUD',
                'base_url' => 'https://config.example',
                'domains' => ['config.example'],
                'path_prefix' => '',
                'is_active' => true,
            ],
        ],
    ];
}

test('DB-backed site context exposes the numeric site id', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $pdo = makeSiteIdResolverPdo();
    $repository = new SiteRepository($pdo);
    $siteId = $repository->create('main', 'Main Site', 'main.example');

    $context = (new SiteContextResolver(siteIdFallbackConfig(), $repository))->resolve('main.example', '/');

    expect($context->siteId)->toBe($siteId);
    expect($context->toArray()['site_id'])->toBe((string) $siteId);
});

test('config fallback site context has no numeric site id', function () {
    $context = (new SiteContextResolver(siteIdFallbackConfig()))->resolve('config.example', '/');

    expect($context->siteId)->toBeNull();
    expect($context->toArray()['site_id'])->toBe('');
});
