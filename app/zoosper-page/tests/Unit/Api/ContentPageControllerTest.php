<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Api;

use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Page\Api\ContentPageController;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Site\Model\Site;
use Zoosper\Site\Repository\SiteRepository;

it('returns structured Editor.js JSON and content format for published pages', function (): void {
    $site = new Site(
        id: 1,
        code: 'main',
        name: 'Main Site',
        status: 'active',
        homepageSlug: 'home',
    );

    $page = new Page(
        id: 10,
        siteId: 1,
        title: 'Welcome Home',
        slug: 'home',
        content: '<p>Welcome</p>',
        status: 'published',
        publishedAt: '2026-08-30 12:00:00',
        contentFormat: 'block_json',
        contentJson: json_encode([
            'time' => 1700000000,
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Welcome']],
            ],
            'version' => '2.30.8',
        ], JSON_THROW_ON_ERROR),
    );

    $siteRepo = new class($site) extends SiteRepository {
        public function __construct(private Site $site) {}
        public function findById(int $id): ?Site { return $this->site->id === $id ? $this->site : null; }
    };

    $pageRepo = new class($page) extends PageRepository {
        public function __construct(private Page $page) {}
        public function findPublishedBySlug(int $siteId, string $slug): ?Page {
            return ($this->page->siteId === $siteId && $this->page->slug === $slug) ? $this->page : null;
        }
    };

    $controller = new ContentPageController(new JsonResponder(), $siteRepo, $pageRepo);

    $request = (new Request('GET', '/api/v1/content/page', query: ['slug' => 'home']))
        ->withSiteContext(new SiteContext(siteId: 1, host: 'example.com', isDefault: true));

    $response = $controller->show($request);

    expect($response->status())->toBe(200);
    $data = json_decode($response->body(), true);

    expect($data['success'])->toBeTrue()
        ->and($data['data']['site']['id'])->toBe(1)
        ->and($data['data']['page']['id'])->toBe(10)
        ->and($data['data']['page']['content_format'])->toBe('block_json')
        ->and($data['data']['page']['content_json'])->toBeArray()
        ->and($data['data']['page']['content_json']['blocks'][0]['data']['text'])->toBe('Welcome')
        ->and($data['data']['page']['content'])->toBe('<p>Welcome</p>');
});
