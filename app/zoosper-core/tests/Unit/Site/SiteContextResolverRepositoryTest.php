<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Site;

use PDO;
use Zoosper\Core\Site\SiteContextResolver;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Phase 1.34c: SiteContextResolver now treats SiteRepository as the primary
 * source of truth and uses config/sites.php only as a bootstrap fallback.
 */

function makeResolverFlipPdo(): PDO
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
function resolverFlipFallbackConfig(): array
{
    return [
        'default_store_view' => 'fallback',
        'store_views' => [
            'fallback' => [
                'website_code' => 'fallback_web',
                'website_name' => 'Fallback Website',
                'store_code' => 'fallback_store',
                'store_name' => 'Fallback Store',
                'store_view_code' => 'fallback',
                'store_view_name' => 'Fallback Store View',
                'locale' => 'en_AU',
                'currency' => 'AUD',
                'base_url' => 'https://fallback.example',
                'domains' => ['fallback.example'],
                'path_prefix' => '',
                'is_active' => true,
            ],
        ],
    ];
}

test('database site wins over config fallback for a matching host', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $pdo = makeResolverFlipPdo();
    $repository = new SiteRepository($pdo);
    $repository->create(
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

    $context = (new SiteContextResolver(resolverFlipFallbackConfig(), $repository))->resolve('nz.example', '/nz/category');

    expect($context->websiteCode)->toBe('anz');
    expect($context->storeCode)->toBe('nz');
    expect($context->storeViewCode)->toBe('nz_en');
    expect($context->locale)->toBe('en_NZ');
    expect($context->currency)->toBe('NZD');
    expect($context->baseUrl)->toBe('https://nz.example');
    expect($context->pathPrefix)->toBe('/nz');
});

test('config fallback is used when no database host matches', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $resolver = new SiteContextResolver(resolverFlipFallbackConfig(), new SiteRepository(makeResolverFlipPdo()));

    $context = $resolver->resolve('fallback.example', '/');

    expect($context->websiteCode)->toBe('fallback_web');
    expect($context->storeViewCode)->toBe('fallback');
    expect($context->baseUrl)->toBe('https://fallback.example');
});

test('database site with a path prefix does not match an unrelated path', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $pdo = makeResolverFlipPdo();
    $repository = new SiteRepository($pdo);
    $repository->create(
        code: 'nz',
        name: 'NZ Store',
        host: 'nz.example',
        pathPrefix: '/nz',
    );

    $context = (new SiteContextResolver(resolverFlipFallbackConfig(), $repository))->resolve('nz.example', '/au');

    expect($context->websiteCode)->toBe('fallback_web');
    expect($context->storeViewCode)->toBe('fallback');
});

test('resolver still works without a SiteRepository', function () {
    $context = (new SiteContextResolver(resolverFlipFallbackConfig()))->resolve('fallback.example', '/');

    expect($context->websiteCode)->toBe('fallback_web');
    expect($context->storeViewCode)->toBe('fallback');
});
