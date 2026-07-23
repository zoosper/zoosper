<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Acl\AclTreeBuilder;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class RoleAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private RoleRepository $roles,
        private AdminLayout $layout,
        private ?AdminUserRepository $users = null,
        private ?AuditLogger $auditLogger = null,
    ) {
    }

    public function index(Request $request): Response
    {
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

    /** @param list<int> $selected */
    private function permissionTree(array $selected): string
    {
        $groups = require dirname(__DIR__, 3) . '/zoosper-auth/config/acl.php';
        $tree = (new AclTreeBuilder())->build($this->roles->allPermissions(), is_array($groups) ? $groups : []);

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
        $path = dirname(__DIR__, 2) . '/resources/views/admin/roles/' . ltrim($template, '/');
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
