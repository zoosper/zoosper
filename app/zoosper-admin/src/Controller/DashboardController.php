<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
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
        private ?AdminViewRenderer $views = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Dashboard',
                template: 'zoosper-admin::dashboard/index',
                data: ['csrfToken' => $this->csrf->token()],
                user: $user,
                active: 'dashboard',
            ));
        }

        return Response::html($this->layout->render(
            'Dashboard',
            '<section class="card"><h2>Welcome to Zoosper</h2><p class="muted">Dashboard is ready.</p></section>',
            $user,
            'dashboard',
        ));
    }
    /**
     * Return the authenticated admin user after the middleware permission gate.
     */
    private function currentAdminUser(): \Zoosper\Auth\Model\AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}
