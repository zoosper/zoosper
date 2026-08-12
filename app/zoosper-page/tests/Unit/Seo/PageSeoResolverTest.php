<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Seo\PageSeoResolver;
use Zoosper\Site\Model\Site;

it('resolves explicit Page SEO with safe canonical and published robots', function (): void {
    $page = new Page(1, 1, 'Page title', 'about', '<p>Body</p>', 'published', metaTitle: 'SEO title', metaDescription: 'Description', canonicalUrl: 'https://example.test/custom');
    $site = new Site(1, 'main', 'Main', 'active', 'home', baseUrl: 'https://example.test');
    $seo = (new PageSeoResolver())->resolve($page, $site, new Request('GET', '/about'));
    expect($seo->title)->toBe('SEO title')->and($seo->description)->toBe('Description')->and($seo->canonicalUrl)->toBe('https://example.test/custom')->and($seo->robots)->toBe('index,follow')->and($seo->openGraphUrl)->toBe($seo->canonicalUrl);
});

it('falls back to Page title, derives a Site canonical and noindexes preview contexts', function (): void {
    $page = new Page(1, 1, 'Home title', 'home', '<p>Body</p>', 'published');
    $site = new Site(1, 'main', 'Main', 'active', 'home', baseUrl: 'https://example.test');
    $resolver = new PageSeoResolver();
    expect($resolver->resolve($page, $site, new Request('GET', '/'))->canonicalUrl)->toBe('https://example.test/')
        ->and($resolver->resolve($page, $site, null)->robots)->toBe('noindex,nofollow')
        ->and($resolver->resolve($page, new Site(1, 'main', 'Main', 'active'), new Request('GET', '/'))->canonicalUrl)->toBeNull();
});
