<?php

declare(strict_types=1);

namespace Zoosper\Menu\Frontend;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Menu\Contract\{BreadcrumbProviderInterface, MenuProviderInterface};
use Zoosper\Page\Contract\FrontendNavigationContributorInterface;
use Zoosper\Theme\Template\TemplateRenderer;

final readonly class MenuFrontendNavigationContributor implements FrontendNavigationContributorInterface
{
    public function __construct(
        private MenuProviderInterface $menus,
        private BreadcrumbProviderInterface $breadcrumbs,
        private TemplateRenderer $templates,
        private string $menuCode = 'main',
    ) {
    }

    public function contribute(SiteContext $siteContext, Request $request): array
    {
        if ($siteContext->siteId === null) {
            return ['navigationHtml' => '', 'breadcrumbsHtml' => ''];
        }

        $path = $request->path();
        $nodes = $this->menus->tree($siteContext->siteId, $this->menuCode, $path);
        $crumbs = $this->breadcrumbs->breadcrumbs($siteContext->siteId, $this->menuCode, $path);

        return [
            'navigationHtml' => $nodes === [] ? '' : $this->templates->render(
                'zoosper-menu::frontend/menu/navigation.latte',
                ['nodes' => $nodes],
                'default',
                'frontend.menu',
                $request,
            ),
            'breadcrumbsHtml' => $crumbs === [] ? '' : $this->templates->render(
                'zoosper-menu::frontend/menu/breadcrumbs.latte',
                ['breadcrumbs' => $crumbs],
                'default',
                'frontend.breadcrumbs',
                $request,
            ),
        ];
    }
}
