<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Dashboard\DashboardQuickLinks;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class DashboardController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
        private AdminMenu $menu,
        private DashboardQuickLinks $quickLinks,
        private ?AdminViewRenderer $views = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $quickLinks = $this->quickLinks->fromMenuItems($this->menu->itemsFor($user));

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Dashboard',
                template: 'zoosper-admin::dashboard/index',
                data: [
                    'csrfToken' => $this->csrf->token(),
                    'quickLinks' => $quickLinks,
                    'workspaceCount' => count($quickLinks),
                ],
                user: $user,
                active: 'dashboard',
            ));
        }

        return Response::html($this->layout->render(
            'Dashboard',
            sprintf(
                '<section class="card"><h2>Admin workspaces</h2><p class="muted">%d available.</p></section>',
                count($quickLinks),
            ),
            $user,
            'dashboard',
        ));
    }

    /** Return the authenticated admin user after the middleware permission gate. */
    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}
