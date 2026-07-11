<?php

declare(strict_types=1);

namespace Zoosper\Page\Service;

use Zoosper\Core\App\CmsVersion;
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
     * Phase 0.62 intentionally renders extensionless template names so the
     * TemplateEngineRegistry can prefer `.latte` templates while keeping `.php`
     * fallback templates available during the migration.
     */
    public function render(Page $page, Site $site): string
    {
        $templates = $this->templates ?? new TemplateRenderer(
            new ThemeResolver(dirname(__DIR__, 4) . '/themes', 'default'),
            $this->modules,
        );
        $themeCode = $site->themeCode;
        $versionLabel = ($this->version ?? new CmsVersion())->label();
        $siteContext = $this->currentSiteContext?->get();

        $data = [
            'page' => $page,
            'site' => $site,
            'siteContext' => $siteContext,
            'versionLabel' => $versionLabel,
        ];

        $content = $templates->render('zoosper-page::page/view', $data, $themeCode);

        return $templates->renderLayout('layout', $content, $data, $themeCode);
    }
}
