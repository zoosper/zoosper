<?php

declare(strict_types=1);

use Zoosper\Seo\Contract\SitemapContributorInterface;
use Zoosper\Seo\Sitemap\SitemapAggregator;
use Zoosper\Seo\Sitemap\SitemapEntry;
use Zoosper\Site\Model\Site;

/** Exercise URL validation through public XML output, not private methods. */
it('preserves sitemap URL acceptance at the scheme boundary', function (string $url, bool $accepted): void {
    $contributor = new class($url) implements SitemapContributorInterface {
        public function __construct(private readonly string $url) {}

        /** @return iterable<SitemapEntry> */
        public function entries(Site $site): iterable
        {
            yield new SitemapEntry($this->url);
        }
    };
    $xml = (new SitemapAggregator([$contributor]))->xml(new Site(1, 'main', 'Main', 'active'));
    expect(substr_count($xml, '<url><loc>'))->toBe($accepted ? 1 : 0);
    if ($accepted) {
        expect($xml)->toContain(htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
    }
})->with([
    'HTTP' => ['http://example.test/a', true],
    'HTTPS' => ['https://example.test/a', true],
    'mixed-case scheme' => ['HtTpS://example.test/a', true],
    'scheme absent' => ['//example.test/a', false],
    'relative path' => ['/a', false],
    'empty' => ['', false],
    'unsupported scheme' => ['ftp://example.test/a', false],
    'script scheme' => ['javascript:bad', false],
    'missing host' => ['https:///a', false],
    'invalid port' => ['https://example.test:99999/a', false],
]);

/** Preserve deterministic aggregation and escaping independently of scheme casing. */
it('preserves sitemap sorting deduplication and XML escaping', function (): void {
    $contributor = new class implements SitemapContributorInterface {
        /** @return iterable<SitemapEntry> */
        public function entries(Site $site): iterable
        {
            yield new SitemapEntry('https://example.test/b');
            yield new SitemapEntry('https://example.test/a?x=1&y="two"');
            yield new SitemapEntry('https://example.test/b');
        }
    };
    $xml = (new SitemapAggregator([$contributor]))->xml(new Site(1, 'main', 'Main', 'active'));
    expect(substr_count($xml, '<url><loc>'))->toBe(2)
        ->and(substr_count($xml, 'https://example.test/b'))->toBe(1)
        ->and($xml)->toContain('https://example.test/a?x=1&amp;y=&quot;two&quot;')
        ->and(strpos($xml, '/a?'))->toBeLessThan(strpos($xml, '/b'));
});

/** Public sitemap URL composition must retain its existing trim and rejection policy. */
it('preserves sitemap URL composition', function (string $base, ?string $expected): void {
    $site = new Site(1, 'main', 'Main', 'active', baseUrl: $base);
    expect((new SitemapAggregator([]))->sitemapUrl($site))->toBe($expected);
})->with([
    'HTTPS base' => ['https://example.test', 'https://example.test/sitemap.xml'],
    'trim and slash' => [' https://example.test/path/// ', 'https://example.test/path/sitemap.xml'],
    'mixed-case base' => ['HtTp://example.test', 'HtTp://example.test/sitemap.xml'],
    'scheme absent' => ['//example.test', null],
    'unsupported' => ['ftp://example.test', null],
    'empty' => ['', null],
    'missing host' => ['https:///path', null],
]);
