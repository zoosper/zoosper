<?php

declare(strict_types=1);

use Zoosper\Page\Model\Page;
use Zoosper\Page\Seo\PageSeoContributor;
use Zoosper\Site\Model\Site;

/** Exercise URL validation through the public contributor, without source assertions. */
it('preserves explicit canonical URL validation and metadata', function (string $url, ?string $expected): void {
    $page = new Page(
        id: 1,
        siteId: 1,
        title: 'Page title',
        slug: 'home',
        content: '',
        status: 'published',
        metaTitle: ' SEO title ',
        metaDescription: ' Description ',
        canonicalUrl: $url,
    );
    $metadata = (new PageSeoContributor())->contribute($page, new Site(1, 'main', 'Main', 'active'));

    expect($metadata)->not->toBeNull()
        ->and($metadata?->canonicalUrl)->toBe($expected)
        ->and($metadata?->openGraphUrl)->toBe($expected)
        ->and($metadata?->title)->toBe('SEO title')
        ->and($metadata?->description)->toBe('Description')
        ->and($metadata?->robots)->toBe('noindex,nofollow');
})->with([
    'https' => ['https://example.test/page', 'https://example.test/page'],
    'http' => ['http://example.test/page', 'http://example.test/page'],
    'uppercase scheme' => ['HTTPS://example.test/page', 'HTTPS://example.test/page'],
    'trimmed' => [' https://example.test/page ', 'https://example.test/page'],
    'missing scheme' => ['//example.test/page', null],
    'relative' => ['/page', null],
    'missing host' => ['https:///page', null],
    'unsupported scheme' => ['ftp://example.test/page', null],
    'executable scheme' => ['javascript:alert(1)', null],
    'empty' => ['', null],
]);

it('declines resources outside the Page model', function (): void {
    expect((new PageSeoContributor())->contribute(new stdClass(), new Site(1, 'main', 'Main', 'active')))->toBeNull();
});
