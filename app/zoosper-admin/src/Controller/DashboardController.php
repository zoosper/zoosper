<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use InvalidArgumentException;
use RuntimeException;
use Zoosper\Admin\Dashboard\DashboardPersonalisationService;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;

final readonly class DashboardController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
        private DashboardPersonalisationService $dashboard,
        private AdminUrlGenerator $urls,
        private ?FlashMessageStoreInterface $flash = null,
        private ?AdminViewRenderer $views = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $dashboard = $this->dashboard->forUser($user);

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Dashboard',
                template: 'zoosper-admin::dashboard/index',
                data: [
                    'csrfToken' => $this->csrf->token(),
                    'personalisationUrl' => $this->urls->url('dashboard/preferences'),
                    'resetPersonalisationUrl' => $this->urls->url('dashboard/preferences/reset'),
                    'availableWidgets' => $dashboard->availableWidgets,
                    'widgets' => $dashboard->visibleWidgets,
                    'hiddenWidgetCodes' => $dashboard->hiddenWidgetCodes,
                    'widgetFailureCount' => $dashboard->failureCount,
                    'dashboardCustomised' => $dashboard->customised,
                ],
                user: $user,
                active: 'dashboard',
            ));
        }

        return Response::html($this->layout->render(
            'Dashboard',
            sprintf('<section class="card"><h2>Dashboard insights</h2><p class="muted">%d available.</p></section>', count($dashboard->visibleWidgets)),
            $user,
            'dashboard',
        ));
    }

    public function savePreferences(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $form = $request->form();

        try {
            $this->dashboard->saveForUser(
                $user,
                $form['known_widgets'] ?? null,
                $form['visible_widgets'] ?? [],
                $form['widget_order'] ?? null,
            );
            $this->flash?->success('Dashboard layout saved.', 'admin.dashboard.preferences');
        } catch (InvalidArgumentException) {
            $this->flash?->error('Dashboard layout could not be saved. Reload the page and try again.', 'admin.dashboard.preferences');
        }

        return Response::redirect($this->urls->url(), 303);
    }

    public function resetPreferences(Request $request): Response
    {
        $this->dashboard->resetForUser($this->currentAdminUser());
        $this->flash?->success('Dashboard layout reset to module defaults.', 'admin.dashboard.preferences');

        return Response::redirect($this->urls->url(), 303);
    }

    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}
