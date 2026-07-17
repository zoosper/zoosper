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
        $rows = '';
        foreach ($this->roles->allRoles() as $role) {
            $id = (int) $role['id'];
            $rows .= '<tr><td>' . $id . '</td><td>' . $this->e((string) $role['label']) . '</td><td><code>' . $this->e((string) $role['code']) . '</code></td><td><a href="/admin/roles/edit?id=' . $id . '">Edit</a></td></tr>';
        }
        return $this->html('Roles & Permissions', '<div class="toolbar"><a class="button" href="/admin/roles/create">Create role</a></div><table><thead><tr><th>ID</th><th>Label</th><th>Code</th><th>Actions</th></tr></thead><tbody>' . $rows . '</tbody></table>');
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
        $token = $this->e($this->csrf->token());
        $code = $this->e((string) ($submitted['code'] ?? $role['code'] ?? ''));
        $label = $this->e((string) ($submitted['label'] ?? $role['label'] ?? ''));
        $roleId = $role !== null ? (int) $role['id'] : null;
        $selectedPermissions = $submitted !== [] ? $this->idsFromForm($submitted, 'permission_ids') : ($roleId !== null ? $this->roles->permissionIdsForRole($roleId) : []);
        $selectedUsers = $submitted !== [] ? $this->idsFromForm($submitted, 'user_ids') : ($roleId !== null ? $this->roles->userIdsForRole($roleId) : []);
        $errorHtml = $error !== null ? '<p class="error">' . $this->e($error) . '</p>' : '';
        $permissionTree = $this->permissionTree($selectedPermissions);
        $userAssignment = $this->userAssignment($selectedUsers);

        return <<<HTML
{$errorHtml}
<form method="post" action="{$action}" class="page-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <label>Role label <input type="text" name="label" value="{$label}" required></label>
    <label>Role code <input type="text" name="code" value="{$code}" required></label>
    <section class="card"><h2>Permission Tree</h2>{$permissionTree}</section>
    <section class="card"><h2>Assigned Users</h2><p class="muted">Search and tick admin users to assign them directly to this role.</p><input type="search" id="role-user-filter" placeholder="Search users by name or email" oninput="document.querySelectorAll('[data-role-user]').forEach(function(row){row.style.display=row.textContent.toLowerCase().includes(event.target.value.toLowerCase())?'flex':'none';})">{$userAssignment}</section>
    <div class="toolbar"><button type="submit">Save role</button><a class="button secondary" href="/admin/roles">Back</a></div>
</form>
HTML;
    }

    /** @param list<int> $selected */
    private function permissionTree(array $selected): string
    {
        $groups = require dirname(__DIR__, 3) . '/zoosper-auth/config/acl.php';
        $tree = (new AclTreeBuilder())->build($this->roles->allPermissions(), is_array($groups) ? $groups : []);
        $html = '';
        foreach ($tree as $group) {
            $html .= '<fieldset><legend>' . $this->e($group->label) . '</legend>';
            foreach ($group->permissions as $permission) {
                $id = (int) $permission['id'];
                $checked = in_array($id, $selected, true) ? ' checked' : '';
                $html .= '<label class="checkbox"><input type="checkbox" name="permission_ids[]" value="' . $id . '"' . $checked . '> <strong>' . $this->e((string) $permission['code']) . '</strong> <span class="muted">' . $this->e((string) $permission['label']) . '</span></label>';
            }
            $html .= '</fieldset>';
        }
        return $html;
    }

    /** @param list<int> $selected */
    private function userAssignment(array $selected): string
    {
        if ($this->users === null) { return '<p class="muted">User assignment requires AdminUserRepository injection.</p>'; }
        $html = '';
        foreach ($this->users->allForAssignment() as $user) {
            $checked = in_array($user->id, $selected, true) ? ' checked' : '';
            $html .= '<label class="checkbox" data-role-user><input type="checkbox" name="user_ids[]" value="' . $user->id . '"' . $checked . '> ' . $this->e($user->name) . ' <span class="muted">' . $this->e($user->email) . '</span></label>';
        }
        return $html !== '' ? $html : '<p class="muted">No admin users found.</p>';
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

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
