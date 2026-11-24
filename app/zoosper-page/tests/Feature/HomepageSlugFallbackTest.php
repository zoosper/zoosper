<?php

declare(strict_types=1);

use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Page\Api\ContentPageController;
use Zoosper\Page\Content\DocumentNormalizer;
use Zoosper\Page\Content\DocumentValidator;
use Zoosper\Page\Controller\PageController;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

function homepageSlugFixture(?string $homepageSlug): array
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE sites (id INTEGER PRIMARY KEY, code TEXT, name TEXT, status TEXT, homepage_slug TEXT)');
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY, site_id INTEGER, title TEXT, slug TEXT, content TEXT, status TEXT, created_by INTEGER, updated_by INTEGER, created_at TEXT, updated_at TEXT, published_at TEXT)');
    $statement = $pdo->prepare('INSERT INTO sites (id, code, name, status, homepage_slug) VALUES (1, :code, :name, :status, :slug)');
    $statement->execute(['code' => 'main', 'name' => 'Main', 'status' => 'active', 'slug' => $homepageSlug]);

    $sites = new SiteRepository($pdo);
    $pages = new PageRepository($pdo);
    foreach (['home' => 'Home marker', 'landing' => 'Landing marker', 'other' => 'Other marker', '0' => 'Zero marker', '' => 'Empty marker'] as $slug => $title) {
        $pages->create(siteId: 1, title: $title, slug: (string) $slug, content: '<p>' . $title . '</p>', status: 'published');
    }

    return [$sites, $pages];
}

function homepageSlugRequest(string $path, array $query = [], string $prefix = ''): Request
{
    return (new Request('GET', $path, query: $query))->withSiteContext(new SiteContext(
        websiteCode: 'main', websiteName: 'Main', storeCode: 'main', storeName: 'Main',
        storeViewCode: 'default', storeViewName: 'Default', locale: 'en_AU', currency: 'AUD',
        baseUrl: 'https://example.com', pathPrefix: $prefix, siteId: 1,
    ));
}

it('uses the configured homepage or home fallback in the content API', function (?string $configured, string $expected): void {
    [$sites, $pages] = homepageSlugFixture($configured);
    $controller = new ContentPageController(new JsonResponder(), $sites, $pages, new DocumentNormalizer(new DocumentValidator()));
    $response = $controller->show(homepageSlugRequest('/api/v1/content/page'));

    expect($response->statusCode())->toBe(200)
        ->and(json_decode($response->body(), true, flags: JSON_THROW_ON_ERROR)['data']['page']['slug'])->toBe($expected);
})->with([[null, 'home'], ['', 'home'], ['0', 'home'], ['landing', 'landing']]);

it('honours scalar API query slugs and falls back for array queries', function (string $configured, array $query, string $expected): void {
    [$sites, $pages] = homepageSlugFixture($configured);
    $controller = new ContentPageController(new JsonResponder(), $sites, $pages, new DocumentNormalizer(new DocumentValidator()));
    $response = $controller->show(homepageSlugRequest('/api/v1/content/page', $query));

    expect($response->statusCode())->toBe(200)
        ->and(json_decode($response->body(), true, flags: JSON_THROW_ON_ERROR)['data']['page']['slug'])->toBe($expected);
})->with([
    ['landing', ['slug' => 'other'], 'other'],
    ['landing', ['slug' => ''], ''],
    ['landing', ['slug' => '0'], '0'],
    ['landing', ['slug' => ['other']], 'landing'],
    ['0', ['slug' => ['other']], 'home'],
]);

it('resolves frontend root and explicit paths independently of the homepage setting', function (?string $configured, string $path, string $prefix, string $expected): void {
    [$sites, $pages] = homepageSlugFixture($configured);
    $controller = new PageController($sites, $pages, new PageRenderer());
    $response = $controller->view(homepageSlugRequest($path, prefix: $prefix));

    expect($response->statusCode())->toBe(200)->and($response->body())->toContain($expected);
})->with([
    [null, '/', '', 'Home marker'],
    ['', '/', '', 'Home marker'],
    ['0', '/', '', 'Home marker'],
    ['landing', '/', '', 'Landing marker'],
    ['landing', '/other', '', 'Other marker'],
    ['landing', '/0', '', 'Zero marker'],
    ['landing', '/shop/', '/shop', 'Landing marker'],
    ['0', '/shop', '/shop', 'Home marker'],
    ['landing', '/shop/other', '/shop', 'Other marker'],
]);