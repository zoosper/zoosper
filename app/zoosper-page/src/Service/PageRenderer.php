<?php

declare(strict_types=1);

namespace Zoosper\Page\Service;

use Zoosper\Core\App\CmsVersion;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Page\Content\BlockJsonToHtmlRenderer;
use Zoosper\Page\Model\Page;
use Zoosper\Site\Model\Site;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\ThemeResolver;

final readonly class PageRenderer
{
    public function __construct(
        private ?TemplateRenderer $templates = null,
        private ?CmsVersion $version = null,
        private ?ModuleRegistry $modules = null,
        private ?BlockJsonToHtmlRenderer $blockJsonRenderer = null,
    ) {
    }

    /**
     * Render a CMS page through the selected frontend theme.
     *
     * Request::siteContext() is the preferred source for frontend requests. Admin
     * preview/non-request callers use an explicit Site-derived SiteContext so the
     * render stack never falls back to a container-held site context.
     */
    public function render(Page $page, Site $site, ?Request $request = null): string
    {
        $templates = $this->templates ?? new TemplateRenderer(
            new ThemeResolver(dirname(__DIR__, 4) . '/themes', 'default'),
            $this->modules,
        );
        $themeCode = $site->themeCode;
        $versionLabel = ($this->version ?? new CmsVersion())->label();
        $siteContext = $request?->siteContext() ?? $this->siteContextFromSite($site);
        $renderedContent = $this->renderContent($page);

        $data = [
            'page' => $page,
            'site' => $site,
            'siteContext' => $siteContext,
            'versionLabel' => $versionLabel,
            'renderedContent' => $renderedContent,
        ];

        $content = $templates->render('zoosper-page::page/view', $data, $themeCode, 'default', $request);

        return $templates->renderLayout('layout', $content, $data, $themeCode, 'default', $request);
    }

    /**
     * Return the frontend body HTML for this page.
     *
     * Existing HTML pages remain backwards compatible. Pages explicitly marked as
     * block_json render from the validated structured document when available and
     * fall back to the saved HTML bridge if the JSON cannot be decoded.
     */
    public function renderContent(Page $page): string
    {
        if (!$page->hasBlockJson()) {
            return $page->content;
        }

        $document = json_decode((string) $page->contentJson, true);
        if (!is_array($document)) {
            return $page->content;
        }

        $html = ($this->blockJsonRenderer ?? new BlockJsonToHtmlRenderer())->render($document);

        return trim($html) !== '' ? $html : $page->content;
    }

    private function siteContextFromSite(Site $site): SiteContext
    {
        return new SiteContext(
            websiteCode: $site->websiteCode,
            websiteName: $site->name,
            storeCode: $site->storeCode,
            storeName: $site->name,
            storeViewCode: $site->storeViewCode,
            storeViewName: $site->name,
            locale: $site->locale,
            currency: $site->currency,
            baseUrl: rtrim($site->baseUrl, '/'),
            pathPrefix: $this->normaliseOptionalPrefix($site->pathPrefix),
            siteId: $site->id,
        );
    }

    private function normaliseOptionalPrefix(string $prefix): string
    {
        $prefix = trim($prefix);
        if ($prefix === '') {
            return '';
        }

        $prefix = '/' . ltrim($prefix, '/');

        return $prefix === '/' ? '' : rtrim($prefix, '/');
    }
}
