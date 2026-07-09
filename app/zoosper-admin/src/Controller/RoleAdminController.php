<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Model\AdminUser;
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
    ) {
    }

    public function index(Request $request): Response
    {
        if ($this->requireRoleManager() === null) {
            return Response::redirect('/admin/login');
        }

        $rows = '';
        foreach ($this->roles->allRoles() as $role) {
            $id = (int) $role['id'];
            $rows .= '<tr><td>' . $id . '</td><td>' . $this->e((string) $role['label']) . '</td><td><code>' . $this->e((string) $role['code']) . '</code></td><td><a href="/admin/roles/edit?id=' . $id . '">Edit</a></td></tr>';
        }

        return $this->html('Roles & Permissions', '<div class="toolbar"><a class="button" href="/admin/roles/create">Create role</a></div><table><thead><tr><th>ID</th><th>Label</th><th>Code</th><th>Actions</th></tr></thead><tbody>' . $rows . '</tbody></table>');
    }

    public function createForm(Request $request): Response
    {
        if ($this->requireRoleManager() === null) {
            return Response::redirect('/admin/login');
        }
        return $this->html('Create Role', $this->form('/admin/roles/create'));
    }

    public function create(Request $request): Response
    {
        if ($this->requireRoleManager() === null) {
            return Response::redirect('/admin/login');
        }
        $form = $request->form();
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return $this->html('Create Role', $this->form('/admin/roles/create', null, 'Invalid security token.', $form), 419);
        }
        try {
            $id = $this->roles->createRole(
                code: (string) ($form['code'] ?? ''),
                label: trim((string) ($form['label'] ?? '')),
                permissionIds: $this->permissionIdsFromForm($form),
            );
            return Response::redirect('/admin/roles/edit?id=' . $id);
        } catch (RuntimeException $exception) {
            return $this->html('Create Role', $this->form('/admin/roles/create', null, $exception->getMessage(), $form), 422);
        }
    }

    public function editForm(Request $request): Response
    {
        if ($this->requireRoleManager() === null) {
            return Response::redirect('/admin/login');
        }
        $role = $this->roleFromRequest($request);
        if ($role === null) {
            return $this->html('Role Not Found', '<p>Role not found.</p>', 404);
        }
        return $this->html('Edit Role', $this->form('/admin/roles/edit?id=' . (int) $role['id'], $role));
    }

    public function update(Request $request): Response
    {
        if ($this->requireRoleManager() === null) {
            return Response::redirect('/admin/login');
        }
        $role = $this->roleFromRequest($request);
        if ($role === null) {
            return $this->html('Role Not Found', '<p>Role not found.</p>', 404);
        }
        $form = $request->form();
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return $this->html('Edit Role', $this->form('/admin/roles/edit?id=' . (int) $role['id'], $role, 'Invalid security token.', $form), 419);
        }
        try {
            $this->roles->updateRole(
                id: (int) $role['id'],
                code: (string) ($form['code'] ?? ''),
                label: trim((string) ($form['label'] ?? '')),
                permissionIds: $this->permissionIdsFromForm($form),
            );
            return Response::redirect('/admin/roles/edit?id=' . (int) $role['id']);
        } catch (RuntimeException $exception) {
            return $this->html('Edit Role', $this->form('/admin/roles/edit?id=' . (int) $role['id'], $role, $exception->getMessage(), $form), 422);
        }
    }

    private function requireRoleManager(): ?AdminUser
    {
        return $this->guard->requirePermission(Permission::RoleManage->value);
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
        $selected = $submitted !== [] ? $this->permissionIdsFromForm($submitted) : ($role !== null ? $this->roles->permissionIdsForRole((int) $role['id']) : []);
        $permissions = $this->permissionCheckboxes($selected);
        $errorHtml = $error !== null ? '<p class="error">' . $this->e($error) . '</p>' : '';

        return <<<HTML
{$errorHtml}
<form method="post" action="{$action}" class="page-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <label>Role label <input type="text" name="label" value="{$label}" required></label>
    <label>Role code <input type="text" name="code" value="{$code}" required></label>
    <fieldset class="card"><legend>Permissions</legend>{$permissions}</fieldset>
    <div class="toolbar"><button type="submit">Save role</button><a class="button secondary" href="/admin/roles">Back</a></div>
</form>
HTML;
    }

    /** @param list<int> $selected */
    private function permissionCheckboxes(array $selected): string
    {
        $html = '';
        foreach ($this->roles->allPermissions() as $permission) {
            $id = (int) $permission['id'];
            $checked = in_array($id, $selected, true) ? ' checked' : '';
            $label = $this->e((string) $permission['code']) . ' — ' . $this->e((string) $permission['label']);
            $html .= '<label class="checkbox"><input type="checkbox" name="permission_ids[]" value="' . $id . '"' . $checked . '> ' . $label . '</label>';
        }
        return $html;
    }

    /** @param array<string, mixed> $form @return list<int> */
    private function permissionIdsFromForm(array $form): array
    {
        $ids = $form['permission_ids'] ?? [];
        if (!is_array($ids)) {
            return [];
        }
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
