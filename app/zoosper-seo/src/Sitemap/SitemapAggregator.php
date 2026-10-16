<?php

declare(strict_types=1);

namespace Zoosper\Seo\Sitemap;

use Zoosper\Seo\Contract\SitemapContributorInterface;
use Zoosper\Site\Model\Site;

/** @psalm-api */
final readonly class SitemapAggregator
{
    /** @param list<SitemapContributorInterface> $contributors */
    public function __construct(private array $contributors) {}

    public function xml(Site $site): string
    {
        $urls = [];
        foreach ($this->contributors as $contributor) {
            foreach ($contributor->entries($site) as $entry) {
                if ($this->valid($entry->url)) {
                    $urls[] = $entry->url;
                }
            }
        }
        $urls = array_values(array_unique($urls));
        sort($urls, SORT_STRING);
        $rows = array_map(static fn (string $url): string => '  <url><loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>', $urls);
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . ($rows === [] ? '' : "\n" . implode("\n", $rows) . "\n") . '</urlset>' . "\n";
    }

    public function sitemapUrl(Site $site): ?string
    {
        $base = rtrim(trim($site->baseUrl), '/');
        return $this->valid($base) ? $base . '/sitemap.xml' : null;
    }

    private function valid(string $url): bool
    {
        $parts = parse_url($url);
        return is_array($parts) && isset($parts['host']) && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true);
    }
}
