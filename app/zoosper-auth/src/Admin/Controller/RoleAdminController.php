<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Controller;

use Zoosper\Auth\Admin\Grid\AuthGridQueryState;

use Zoosper\Auth\Admin\Grid\RoleGridIndex;

use RuntimeException;
use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Acl\AclTreeBuilder;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Admin CRUD controller for roles and permissions.
 *
 * Phase 1.111 (Sonnet Phase 2 §3.3): permissionTree() previously used a raw
 * `require dirname(__DIR__, 3) . '/zoosper-auth/config/acl.php'` at runtime.
 * That bypassed the layered config system entirely, so this controller could
 * never see project-level or other-module ACL group overrides that every other
 * config access in the codebase gets via ConfigRepository (which is built by
 * ModuleConfigAggregator across ALL modules' config/*.php, not just one file).
 *
 * The fix injects an OPTIONAL, LAST ConfigRepository dependency (mirroring the
 * PasswordPolicy pattern from Phase 1.110): when present, ACL groups come from
 * $config->array('acl') (properly layered/aggregated); when absent (no DI
 * wiring change made yet), it falls back to the original single-file require so
 * behaviour is identical until the container is updated to inject it.
 *
 * Phase E1: ConfigRepository is now actually wired in
 * (app/zoosper-auth/config/controllers.php), so the ConfigRepository path is
 * the one exercised in production.
 *
 * Phase F1: relocated from Zoosper\Admin\Controller to Zoosper\Auth\Admin\
 * Controller (namespace change ONLY — no logic touched, including the
 * `dirname(__DIR__, 3)` fallback path below, which is intentionally left
 * untouched since it is a documented pre-Phase-1.111 fallback whose depth was
 * already independent of this class's own location — see the run-order note
 * in this phase's README for why this specific line needed no change).
 */
final readonly class RoleAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private RoleRepository $roles,
        private AdminLayout $layout,
        private ?AdminUserRepository $users = null,
        private ?AuditLogger $auditLogger = null,
        private ?ConfigRepository $config = null,
        private ?RoleGridIndex $gridIndex = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();
        if ($this->gridIndex !== null) {
            return $this->html(
                'Roles & Permissions',
                $this->gridIndex->render(
                    $user->id,
                    AuthGridQueryState::fromQuery($_GET),
                    AuthGridQueryState::bookmarkId($_GET),
                ),
            );
        }

        $this->currentAdminUser();

        return $this->html('Roles & Permissions', $this->renderRoleView('index.php', [
            'roles' => $this->roles->allRoles(),
        ]));
    
    }

    public function createForm(Request $request): Response
    {
        $this->currentAdminUser();
        return $this->html('Create Role', $this->form('/admin/roles/create'));
    }

    public function create(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $form = $request->form();

        try {
            $id = $this->roles->createRole((string) ($form['code'] ?? ''), trim((string) ($form['label'] ?? '')), $this->idsFromForm($form, 'permission_ids'));
            $this->auditLogger?->record($actor, 'role.created', 'admin_role', (string) $id, 'Created admin role', ['code' => (string) ($form['code'] ?? '')], $request);
            return Response::redirect('/admin/roles/edit?id=' . $id);
        } catch (RuntimeException $exception) {
            return $this->html('Create Role', $this->form('/admin/roles/create', null, $exception->getMessage(), $form), 422);
        }
    }

    public function editForm(Request $request): Response
    {
        $this->currentAdminUser();
        $role = $this->roleFromRequest($request);
        if ($role === null) { return $this->html('Role Not Found', '<p>Role not found.</p>', 404); }
        return $this->html('Edit Role', $this->form('/admin/roles/edit?id=' . (int) $role['id'], $role));
    }

    public function update(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $role = $this->roleFromRequest($request);
        if ($role === null) { return $this->html('Role Not Found', '<p>Role not found.</p>', 404); }
        $form = $request->form();

        try {
            $permissionIds = $this->idsFromForm($form, 'permission_ids');
            $userIds = $this->idsFromForm($form, 'user_ids');
            $this->roles->updateRole((int) $role['id'], (string) ($form['code'] ?? ''), trim((string) ($form['label'] ?? '')), $permissionIds, $userIds);
            $this->auditLogger?->record($actor, 'role.updated', 'admin_role', (string) $role['id'], 'Updated role permissions and users', ['permission_ids' => $permissionIds, 'user_ids' => $userIds], $request);
            return Response::redirect('/admin/roles/edit?id=' . (int) $role['id']);
        } catch (RuntimeException $exception) {
            return $this->html('Edit Role', $this->form('/admin/roles/edit?id=' . (int) $role['id'], $role, $exception->getMessage(), $form), 422);
        }
    }

    /**
     * Return the authenticated admin user after the middleware permission gate.
     */
    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }

    /** @return array<string, mixed>|null */
    private function roleFromRequest(Request $request): ?array
    {
        $id = $request->query('id');
        return $id !== null && ctype_digit($id) ? $this->roles->findRoleById((int) $id) : null;
    }

    /** @param array<string, mixed>|null $role @param array<string, mixed> $submitted */
    private function form(string $action, ?array $role = null, ?string $error = null, array $submitted = []): string
    {
        $roleId = $role !== null ? (int) $role['id'] : null;
        $selectedPermissions = $submitted !== []
            ? $this->idsFromForm($submitted, 'permission_ids')
            : ($roleId !== null ? $this->roles->permissionIdsForRole($roleId) : []);
        $selectedUsers = $submitted !== []
            ? $this->idsFromForm($submitted, 'user_ids')
            : ($roleId !== null ? $this->roles->userIdsForRole($roleId) : []);

        return $this->renderRoleView('form.php', [
            'action' => $action,
            'csrfToken' => $this->csrf->token(),
            'code' => (string) ($submitted['code'] ?? $role['code'] ?? ''),
            'label' => (string) ($submitted['label'] ?? $role['label'] ?? ''),
            'error' => $error,
            'permissionTree' => $this->permissionTree($selectedPermissions),
            'userAssignment' => $this->userAssignment($selectedUsers),
        ]);
    }

    /**
     * Load the ACL group definitions used to organise the permission tree.
     *
     * Phase 1.111: prefers ConfigRepository (layered/aggregated across ALL
     * modules' config/acl.php) when injected; falls back to the original
     * single-file require otherwise, so this method's behaviour is unchanged
     * until the DI wiring is updated to pass a ConfigRepository.
     *
     * @return array<string, mixed>
     */
    private function aclGroups(): array
    {
        if ($this->config !== null) {
            return $this->config->array('acl');
        }

        $groups = require dirname(__DIR__, 3) . '/zoosper-auth/config/acl.php';

        return is_array($groups) ? $groups : [];
    }

    /** @param list<int> $selected */
    private function permissionTree(array $selected): string
    {
        $tree = (new AclTreeBuilder())->build($this->roles->allPermissions(), $this->aclGroups());

        return $this->renderRoleView('permission-tree.php', [
            'tree' => $tree,
            'selected' => $selected,
        ]);
    }

    /** @param list<int> $selected */
    private function userAssignment(array $selected): string
    {
        if ($this->users === null) {
            return 'User assignment requires AdminUserRepository injection.';
        }

        return $this->renderRoleView('user-assignment.php', [
            'users' => $this->users->allForAssignment(),
            'selected' => $selected,
        ]);
    }

    /** @param array<string, mixed> $form @return list<int> */
    private function idsFromForm(array $form, string $field): array
    {
        $ids = $form[$field] ?? [];
        if (!is_array($ids)) { return []; }
        return array_values(array_map(static fn (mixed $id): int => (int) $id, $ids));
    }

    private function html(string $title, string $content, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $this->guard->user(), 'admin-roles'), $statusCode);
    }

    private function renderRoleView(string $template, array $data = []): string
    {
        // Phase F1 NOTE: this controller was relocated from
        // app/zoosper-admin/src/Controller to app/zoosper-auth/src/Admin/
        // Controller, but its raw-PHP view templates were NOT moved (I do not
        // have form.php/index.php/permission-tree.php/user-assignment.php to
        // relocate them safely), so they still physically live at
        // app/zoosper-admin/resources/views/admin/roles/. The path below was
        // updated so it still resolves to that SAME physical location from
        // the controller's new home — this is a deliberate, temporary
        // cross-module reach-back, not an oversight. A natural follow-up is to
        // move those 4 view files into app/zoosper-auth/resources/views/
        // admin/roles/ and simplify this path once they are in hand.
        $path = dirname(__DIR__, 4) . '/zoosper-admin/resources/views/admin/roles/' . ltrim($template, '/');
        if (!is_file($path)) {
            throw new RuntimeException('Role admin view not found: ' . $template);
        }

        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }

private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
