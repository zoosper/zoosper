<?php

declare(strict_types=1);

namespace Zoosper\Page\Seo;

use Zoosper\Core\Http\Request;
use Zoosper\Page\Model\Page;
use Zoosper\Site\Model\Site;

final readonly class PageSeoResolver
{
    public function resolve(Page $page, Site $site, ?Request $request): PageSeoMetadata
    {
        $title = $this->value($page->metaTitle) ?? $page->title;
        $description = $this->value($page->metaDescription);
        $canonical = $this->canonical($page, $site, $request);
        $robots = $page->isPublished() && $request !== null ? 'index,follow' : 'noindex,nofollow';

        return new PageSeoMetadata($title, $description, $canonical, $robots, $title, $description, $canonical);
    }

    private function canonical(Page $page, Site $site, ?Request $request): ?string
    {
        $explicit = $this->value($page->canonicalUrl);
        if ($explicit !== null) {
            $parts = parse_url($explicit);
            return is_array($parts) && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
                && isset($parts['host']) ? $explicit : null;
        }
        $base = rtrim($site->baseUrl, '/');
        if ($base === '' || $request === null || !$page->isPublished()) {
            return null;
        }
        $parts = parse_url($base);
        if (!is_array($parts) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true) || !isset($parts['host'])) {
            return null;
        }
        return $base . ($page->slug === ($site->homepageSlug ?? 'home') ? '/' : '/' . ltrim($page->slug, '/'));
    }

    private function value(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }
}
