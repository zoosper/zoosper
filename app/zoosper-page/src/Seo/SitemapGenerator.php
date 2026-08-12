<?php

declare(strict_types=1);

namespace Zoosper\Page\Seo;

use Zoosper\Page\Model\Page;
use Zoosper\Site\Model\Site;

final readonly class SitemapGenerator
{
    /** @param list<Page> $pages */
    public function xml(Site $site, array $pages): string
    {
        $urls = [];
        foreach ($pages as $page) {
            $url = $this->pageUrl($site, $page);
            if ($url !== null) {
                $urls[] = $url;
            }
        }
        $rows = array_map(static fn (string $url): string => '  <url><loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>', array_values(array_unique($urls)));
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . ($rows === [] ? '' : "\n" . implode("\n", $rows) . "\n") . '</urlset>' . "\n";
    }

    public function sitemapUrl(Site $site): ?string
    {
        $base = $this->baseUrl($site);
        return $base === null ? null : $base . '/sitemap.xml';
    }

    private function pageUrl(Site $site, Page $page): ?string
    {
        if (!$page->isPublished()) {
            return null;
        }
        $explicit = trim((string) $page->canonicalUrl);
        if ($explicit !== '') {
            return $this->absoluteHttpUrl($explicit) ? $explicit : null;
        }
        $base = $this->baseUrl($site);
        if ($base === null) {
            return null;
        }
        return $base . ($page->slug === ($site->homepageSlug ?? 'home') ? '/' : '/' . ltrim($page->slug, '/'));
    }

    private function baseUrl(Site $site): ?string
    {
        $base = rtrim(trim($site->baseUrl), '/');
        return $this->absoluteHttpUrl($base) ? $base : null;
    }

    private function absoluteHttpUrl(string $url): bool
    {
        $parts = parse_url($url);
        return is_array($parts) && isset($parts['host']) && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true);
    }
}
