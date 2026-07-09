<?php

declare(strict_types=1);

namespace Zoosper\Page\Service;

use Zoosper\Core\App\CmsVersion;
use Zoosper\Page\Model\Page;
use Zoosper\Site\Model\Site;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\ThemeResolver;

final readonly class PageRenderer
{
    public function __construct(
        private ?TemplateRenderer $templates = null,
        private ?CmsVersion $version = null,
        private ?string $themeCode = null,
    ) {
    }

    public function render(Page $page, Site $site): string
    {
        $templates = $this->templates ?? new TemplateRenderer(
            new ThemeResolver(dirname(__DIR__, 4) . '/themes', 'default'),
        );

        return $templates->render('page.php', [
            'page' => $page,
            'site' => $site,
            'versionLabel' => ($this->version ?? new CmsVersion())->label(),
        ], $this->themeCode);
    }
}
