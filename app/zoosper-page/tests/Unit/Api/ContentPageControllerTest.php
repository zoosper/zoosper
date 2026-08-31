<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Api;

use PDO;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Page\Api\ContentPageController;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Site\Repository\SiteRepository;

it('returns structured Editor.js JSON and content format for published pages', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE sites (
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
    )');
    $pdo->exec('CREATE TABLE site_domains (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        site_id INTEGER NOT NULL,
        host TEXT NOT NULL,
        is_primary INTEGER NOT NULL DEFAULT 0,
        created_at TEXT,
        updated_at TEXT
    )');
    $pdo->exec('CREATE TABLE pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        site_id INTEGER,
        title TEXT,
        slug TEXT,
        content TEXT,
        status TEXT,
        created_by INTEGER NULL,
        updated_by INTEGER NULL,
        created_at TEXT,
        updated_at TEXT,
        published_at TEXT NULL,
        content_format TEXT NULL,
        content_json TEXT NULL,
        meta_title TEXT NULL,
        meta_description TEXT NULL,
        meta_keywords TEXT NULL,
        og_title TEXT NULL,
        og_description TEXT NULL,
        og_image TEXT NULL,
        canonical_url TEXT NULL,
        robots TEXT NULL,
        structured_data_json TEXT NULL
    )');

    $siteRepo = new SiteRepository($pdo);
    $siteId = $siteRepo->create(code: 'main', name: 'Main Site', host: 'example.com');

    $pageRepo = new PageRepository($pdo);
    $editorJson = json_encode([
        'time' => 1700000000,
        'blocks' => [
            ['type' => 'paragraph', 'data' => ['text' => 'Welcome']],
        ],
        'version' => '2.30.8',
    ], JSON_THROW_ON_ERROR);

    $pageId = $pageRepo->create(
        siteId: $siteId,
        title: 'Welcome Home',
        slug: 'home',
        content: '<p>Welcome</p>',
        status: 'published',
        contentFormat: 'block_json',
        contentJson: $editorJson,
    );

    $controller = new ContentPageController(new JsonResponder(), $siteRepo, $pageRepo);

    $request = (new Request('GET', '/api/v1/content/page', query: ['slug' => 'home']))
        ->withSiteContext(new SiteContext(
            websiteCode: 'main',
            websiteName: 'Main Website',
            storeCode: 'main',
            storeName: 'Main Store',
            storeViewCode: 'default',
            storeViewName: 'Default Store View',
            locale: 'en_AU',
            currency: 'AUD',
            baseUrl: 'https://example.com',
            siteId: $siteId,
        ));

    $response = $controller->show($request);

    expect($response->statusCode())->toBe(200);
    $data = json_decode($response->body(), true);

    expect($data['success'])->toBeTrue()
        ->and($data['data']['site']['id'])->toBe($siteId)
        ->and($data['data']['page']['id'])->toBe($pageId)
        ->and($data['data']['page']['content_format'])->toBe('block_json')
        ->and($data['data']['page']['content_json'])->toBeArray()
        ->and($data['data']['page']['content_json']['blocks'][0]['data']['text'])->toBe('Welcome')
        ->and($data['data']['page']['content'])->toBe('<p>Welcome</p>');
});










