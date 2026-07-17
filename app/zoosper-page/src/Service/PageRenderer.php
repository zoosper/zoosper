<?php

declare(strict_types=1);

namespace Zoosper\Page\Service;

use Zoosper\Core\App\CmsVersion;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Site\CurrentSiteContext;
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
        private ?CurrentSiteContext $currentSiteContext = null,
    ) {
    }

    /**
     * Render a CMS page through the selected frontend theme.
     *
     * Phase 1.34d foundation: an optional Request can be threaded into the
     * template renderer so shared view context/cache dimensions use the immutable
     * request-carried SiteContext instead of global $_SERVER reads. Existing
     * callers remain compatible and use the legacy immutable CurrentSiteContext
     * fallback until the PageController thread is completed.
     */
    public function render(Page $page, Site $site, ?Request $request = null): string
    {
        $templates = $this->templates ?? new TemplateRenderer(
            new ThemeResolver(dirname(__DIR__, 4) . '/themes', 'default'),
            $this->modules,
        );
        $themeCode = $site->themeCode;
        $versionLabel = ($this->version ?? new CmsVersion())->label();
        $siteContext = $request?->siteContext() ?? $this->currentSiteContext?->get();

        $data = [
            'page' => $page,
            'site' => $site,
            'siteContext' => $siteContext,
            'versionLabel' => $versionLabel,
        ];

        $content = $templates->render('zoosper-page::page/view', $data, $themeCode, 'default', $request);

        return $templates->renderLayout('layout', $content, $data, $themeCode, 'default', $request);
    }
}
