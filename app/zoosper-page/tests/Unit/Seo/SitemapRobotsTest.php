<?php

declare(strict_types=1);

use Zoosper\Page\Model\Page;
use Zoosper\Page\Seo\SitemapGenerator;
use Zoosper\Site\Model\Site;

it('emits deterministic escaped published Page URLs only', function (): void {
    $site = new Site(1, 'main', 'Main', 'active', 'home', baseUrl: 'https://example.test');
    $pages = [
        new Page(2, 1, 'About', 'about', '', 'published', canonicalUrl: 'https://example.test/a?x=1&y=2'),
        new Page(1, 1, 'Home', 'home', '', 'published'),
        new Page(3, 1, 'Draft', 'draft', '', 'draft'),
    ];
    $generator = new SitemapGenerator();
    $xml = $generator->xml($site, $pages);
    expect($xml)->toContain('<loc>https://example.test/a?x=1&amp;y=2</loc>')->toContain('<loc>https://example.test/</loc>')->not->toContain('/draft')->and($generator->sitemapUrl($site))->toBe('https://example.test/sitemap.xml');
});

it('does not invent URLs without a valid absolute Site base URL', function (): void {
    $site = new Site(1, 'main', 'Main', 'active', 'home');
    $xml = (new SitemapGenerator())->xml($site, [new Page(1, 1, 'Home', 'home', '', 'published')]);
    expect($xml)->not->toContain('<loc>')->and((new SitemapGenerator())->sitemapUrl($site))->toBeNull();
});
