<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class LoginHistoryController
{
    public function __construct(
        private SessionGuard $guard,
        private LoginHistoryRepository $history,
        private AdminLayout $layout,
        private ?AdminViewRenderer $views = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $rows = $this->history->latest();
        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Login History',
                template: 'zoosper-admin::login-history/index',
                data: ['rows' => $rows],
                user: $user,
                active: 'login-history',
            ));
        }

        return Response::html($this->layout->render('Login History', '<p>Login history view renderer is not configured.</p>', $user, 'login-history'));
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
