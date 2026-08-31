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
use Zoosper\Audit\Contract\AuditLoggerInterface;
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
        private ?AuditLoggerInterface $audit = null,
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
                    'roleDefaultsUrl' => $user->can('role.manage') ? $this->urls->url('dashboard/role-defaults') : null,
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
        $this->flash?->success('Dashboard layout reset to assigned-role or module defaults.', 'admin.dashboard.preferences');

        return Response::redirect($this->urls->url(), 303);
    }

    public function roleDefaults(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $roles = $this->dashboard->roles();
        $roleId = (int) ($request->query('role_id') ?? ($roles[0]->id ?? 0));
        $selectedRole = null;
        foreach ($roles as $role) {
            if ($role->id === $roleId) {
                $selectedRole = $role;
                break;
            }
        }
        if ($selectedRole === null && $roles !== []) {
            $selectedRole = $roles[0];
        }
        $roleDashboard = $selectedRole === null ? null : $this->dashboard->forRoleEditor($actor, $selectedRole->id);

        if ($this->views === null) {
            return Response::html($this->layout->render('Dashboard role defaults', '<section class="card"><h1>Dashboard role defaults</h1></section>', $actor, 'dashboard-role-defaults'));
        }

        return Response::html($this->views->render(
            title: 'Dashboard role defaults',
            template: 'zoosper-admin::dashboard/role-defaults',
            data: [
                'csrfToken' => $this->csrf->token(),
                'roles' => $roles,
                'selectedRole' => $selectedRole,
                'availableWidgets' => $roleDashboard?->availableWidgets ?? [],
                'hiddenWidgetCodes' => $roleDashboard?->hiddenWidgetCodes ?? [],
                'roleDefaultConfigured' => $roleDashboard?->customised ?? false,
                'roleDefaultsUrl' => $this->urls->url('dashboard/role-defaults'),
                'saveRoleDefaultsUrl' => $this->urls->url('dashboard/role-defaults'),
                'resetRoleDefaultsUrl' => $this->urls->url('dashboard/role-defaults/reset'),
                'dashboardUrl' => $this->urls->url(),
            ],
            user: $actor,
            active: 'dashboard-role-defaults',
        ));
    }

    public function saveRoleDefaults(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $form = $request->form();
        $roleId = (int) ($form['role_id'] ?? 0);

        try {
            $this->dashboard->saveRoleDefault(
                $actor,
                $roleId,
                $form['known_widgets'] ?? null,
                $form['visible_widgets'] ?? [],
                $form['widget_order'] ?? null,
            );
            $this->audit?->record($actor, 'dashboard.role-default.updated', 'admin_role', (string) $roleId, 'Updated Dashboard role defaults', [], $request);
            $this->flash?->success('Dashboard role defaults saved.', 'admin.dashboard.role-defaults');
        } catch (InvalidArgumentException|RuntimeException) {
            $this->flash?->error('Dashboard role defaults could not be saved. Reload the page and try again.', 'admin.dashboard.role-defaults');
        }

        return Response::redirect($this->roleDefaultsLocation($roleId), 303);
    }

    public function resetRoleDefaults(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $roleId = (int) (($request->form())['role_id'] ?? 0);

        try {
            $this->dashboard->resetRoleDefault($roleId);
            $this->audit?->record($actor, 'dashboard.role-default.reset', 'admin_role', (string) $roleId, 'Reset Dashboard role defaults', [], $request);
            $this->flash?->success('Dashboard role defaults reset to module defaults.', 'admin.dashboard.role-defaults');
        } catch (RuntimeException) {
            $this->flash?->error('Dashboard role defaults could not be reset.', 'admin.dashboard.role-defaults');
        }

        return Response::redirect($this->roleDefaultsLocation($roleId), 303);
    }

    private function roleDefaultsLocation(int $roleId): string
    {
        return $this->urls->url('dashboard/role-defaults') . ($roleId > 0 ? '?role_id=' . $roleId : '');
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










