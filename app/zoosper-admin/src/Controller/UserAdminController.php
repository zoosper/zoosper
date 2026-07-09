<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class UserAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminUserRepository $users,
        private RoleRepository $roles,
        private PasswordHasher $passwordHasher,
        private AdminLayout $layout,
    ) {
    }

    public function index(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        $rows = '';
        foreach ($this->users->all() as $user) {
            $rows .= '<tr><td>' . $user->id . '</td><td>' . $this->e($user->name) . '</td><td>' . $this->e($user->email) . '</td><td>' . $this->e($user->status) . '</td><td><a href="/admin/users/edit?id=' . $user->id . '">Edit</a></td></tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="5">No admin users yet.</td></tr>';
        }

        return $this->html('Admin Users', '<div class="toolbar"><a class="button" href="/admin/users/create">Create admin user</a></div><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead><tbody>' . $rows . '</tbody></table>');
    }

    public function createForm(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        return $this->html('Create Admin User', $this->form('/admin/users/create'));
    }

    public function create(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        $form = $request->form();
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return $this->html('Create Admin User', $this->form('/admin/users/create', null, 'Invalid security token.', $form), 419);
        }

        try {
            $password = (string) ($form['password'] ?? '');
            if ($password === '') {
                throw new RuntimeException('Password is required for new admin users.');
            }

            $id = $this->users->createWithRoleIds(
                email: trim((string) ($form['email'] ?? '')),
                name: trim((string) ($form['name'] ?? '')),
                hash: $this->passwordHasher->hash($password),
                status: (string) ($form['status'] ?? 'active'),
                roleIds: $this->roleIdsFromForm($form),
            );

            return Response::redirect('/admin/users/edit?id=' . $id);
        } catch (RuntimeException $exception) {
            return $this->html('Create Admin User', $this->form('/admin/users/create', null, $exception->getMessage(), $form), 422);
        }
    }

    public function editForm(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        $user = $this->userFromRequest($request);
        if ($user === null) {
            return $this->html('Admin User Not Found', '<p>Admin user not found.</p>', 404);
        }

        return $this->html('Edit Admin User', $this->form('/admin/users/edit?id=' . $user->id, $user));
    }

    public function update(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        $user = $this->userFromRequest($request);
        if ($user === null) {
            return $this->html('Admin User Not Found', '<p>Admin user not found.</p>', 404);
        }

        $form = $request->form();
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return $this->html('Edit Admin User', $this->form('/admin/users/edit?id=' . $user->id, $user, 'Invalid security token.', $form), 419);
        }

        try {
            $this->users->updateUser(
                id: $user->id,
                email: trim((string) ($form['email'] ?? '')),
                name: trim((string) ($form['name'] ?? '')),
                status: (string) ($form['status'] ?? 'active'),
                roleIds: $this->roleIdsFromForm($form),
            );

            $password = trim((string) ($form['password'] ?? ''));
            if ($password !== '') {
                $this->users->updatePassword($user->id, $this->passwordHasher->hash($password));
            }

            return Response::redirect('/admin/users/edit?id=' . $user->id);
        } catch (RuntimeException $exception) {
            return $this->html('Edit Admin User', $this->form('/admin/users/edit?id=' . $user->id, $user, $exception->getMessage(), $form), 422);
        }
    }

    private function requireUserManager(): ?AdminUser
    {
        return $this->guard->requirePermission(Permission::RoleManage->value) ?? $this->guard->requirePermission('user.manage');
    }

    private function userFromRequest(Request $request): ?AdminUser
    {
        $id = $request->query('id');
        return $id !== null && ctype_digit($id) ? $this->users->findById((int) $id) : null;
    }

    /** @param array<string, mixed> $submitted */
    private function form(string $action, ?AdminUser $user = null, ?string $error = null, array $submitted = []): string
    {
        $token = $this->e($this->csrf->token());
        $name = $this->e((string) ($submitted['name'] ?? $user?->name ?? ''));
        $email = $this->e((string) ($submitted['email'] ?? $user?->email ?? ''));
        $status = (string) ($submitted['status'] ?? $user?->status ?? 'active');
        $selectedRoles = $submitted !== [] ? $this->roleIdsFromForm($submitted) : ($user !== null ? $this->users->roleIdsForUser($user->id) : []);
        $errorHtml = $error !== null ? '<p class="error">' . $this->e($error) . '</p>' : '';
        $roleOptions = $this->roleCheckboxes($selectedRoles);
        $activeSelected = $status === 'active' ? ' selected' : '';
        $disabledSelected = $status === 'disabled' ? ' selected' : '';

        return <<<HTML
{$errorHtml}
<form method="post" action="{$action}" class="page-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <label>Name <input type="text" name="name" value="{$name}" required></label>
    <label>Email <input type="email" name="email" value="{$email}" required></label>
    <label>Password <input type="password" name="password" autocomplete="new-password"><span class="muted">Leave blank to keep existing password.</span></label>
    <label>Status <select name="status"><option value="active"{$activeSelected}>Active</option><option value="disabled"{$disabledSelected}>Disabled</option></select></label>
    <fieldset class="card"><legend>Roles</legend>{$roleOptions}</fieldset>
    <div class="toolbar"><button type="submit">Save user</button><a class="button secondary" href="/admin/users">Back</a></div>
</form>
HTML;
    }

    /** @param list<int> $selected */
    private function roleCheckboxes(array $selected): string
    {
        $html = '';
        foreach ($this->roles->allRoles() as $role) {
            $id = (int) $role['id'];
            $checked = in_array($id, $selected, true) ? ' checked' : '';
            $label = $this->e((string) $role['label']);
            $html .= '<label class="checkbox"><input type="checkbox" name="role_ids[]" value="' . $id . '"' . $checked . '> ' . $label . '</label>';
        }
        return $html;
    }

    /** @param array<string, mixed> $form */
    private function roleIdsFromForm(array $form): array
    {
        $ids = $form['role_ids'] ?? [];
        if (!is_array($ids)) {
            return [];
        }
        return array_values(array_map(static fn (mixed $id): int => (int) $id, $ids));
    }

    private function html(string $title, string $content, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $this->guard->user(), 'admin-users'), $statusCode);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
