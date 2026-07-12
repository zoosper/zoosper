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
use Zoosper\TwoFactor\Service\AdminTwoFactorResetService;

final readonly class UserAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminUserRepository $users,
        private RoleRepository $roles,
        private PasswordHasher $passwordHasher,
        private AdminLayout $layout,
        private ?AdminTwoFactorResetService $twoFactorReset = null,
    ) {
    }

    /**
     * Show the admin user listing.
     */
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

    /**
     * Show the create-admin-user form.
     */
    public function createForm(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        return $this->html('Create Admin User', $this->form('/admin/users/create'));
    }

    /**
     * Persist a new admin user.
     */
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

            return Response::redirect('/admin/users/edit?id=' . $id . '&notice=created');
        } catch (RuntimeException $exception) {
            return $this->html('Create Admin User', $this->form('/admin/users/create', null, $exception->getMessage(), $form), 422);
        }
    }

    /**
     * Show the edit-admin-user form with optional status messaging.
     */
    public function editForm(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        $user = $this->userFromRequest($request);
        if ($user === null) {
            return $this->html('Admin User Not Found', '<p>Admin user not found.</p>', 404);
        }

        return $this->html(
            'Edit Admin User',
            $this->noticeFromRequest($request) . $this->form('/admin/users/edit?id=' . $user->id, $user),
        );
    }

    /**
     * Update an admin user or reset their 2FA state from the same edit route.
     *
     * The reset action is CSRF-protected and permission-protected. It never
     * reads, displays or logs OTPs, TOTP secrets, recovery-code plaintext,
     * provisioning URIs, QR data, SMTP passwords or reset tokens.
     */
    public function update(Request $request): Response
    {
        $actor = $this->requireUserManager();
        if ($actor === null) {
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

        if (($form['_action'] ?? '') === 'reset_2fa') {
            return $this->resetTwoFactor($user, $actor);
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

            return Response::redirect('/admin/users/edit?id=' . $user->id . '&notice=saved');
        } catch (RuntimeException $exception) {
            return $this->html('Edit Admin User', $this->form('/admin/users/edit?id=' . $user->id, $user, $exception->getMessage(), $form), 422);
        }
    }

    /**
     * Reset a user's 2FA state so they can enrol again.
     */
    private function resetTwoFactor(AdminUser $targetUser, AdminUser $actor): Response
    {
        if ($this->twoFactorReset === null) {
            return Response::redirect('/admin/users/edit?id=' . $targetUser->id . '&notice=2fa_unavailable');
        }

        try {
            $this->twoFactorReset->reset($targetUser->id, $actor->id);
        } catch (\Throwable) {
            return Response::redirect('/admin/users/edit?id=' . $targetUser->id . '&notice=2fa_failed');
        }

        return Response::redirect('/admin/users/edit?id=' . $targetUser->id . '&notice=2fa_reset');
    }

    /**
     * Require an admin user with user/role management permission.
     */
    private function requireUserManager(): ?AdminUser
    {
        return $this->guard->requirePermission(Permission::RoleManage->value) ?? $this->guard->requirePermission('user.manage');
    }

    /**
     * Resolve the target admin user from the `id` query parameter.
     */
    private function userFromRequest(Request $request): ?AdminUser
    {
        $id = $request->query('id');
        return $id !== null && ctype_digit($id) ? $this->users->findById((int) $id) : null;
    }

    /**
     * Render an escaped notice based on a query-string code.
     */
    private function noticeFromRequest(Request $request): string
    {
        return match ($request->query('notice')) {
            'created' => $this->notice('success', 'Admin user created.'),
            'saved' => $this->notice('success', 'Admin user saved.'),
            '2fa_reset' => $this->notice('success', '2FA reset completed. The admin user can enrol again on their next login.'),
            '2fa_unavailable' => $this->notice('error', '2FA reset service is not available.'),
            '2fa_failed' => $this->notice('error', '2FA reset failed. Check application logs for non-sensitive error details.'),
            default => '',
        };
    }

    /**
     * Render a status notice block.
     */
    private function notice(string $type, string $message): string
    {
        return '<div class="notice notice-' . $this->e($type) . '">' . $this->e($message) . '</div>';
    }

    /**
     * Render the create/edit form.
     *
     * @param array<string, mixed> $submitted
     */
    private function form(string $action, ?AdminUser $user = null, ?string $error = null, array $submitted = []): string
    {
        $token = $this->e($this->csrf->token());
        $escapedAction = $this->e($action);
        $name = $this->e((string) ($submitted['name'] ?? $user?->name ?? ''));
        $email = $this->e((string) ($submitted['email'] ?? $user?->email ?? ''));
        $status = (string) ($submitted['status'] ?? $user?->status ?? 'active');
        $selectedRoles = $submitted !== [] ? $this->roleIdsFromForm($submitted) : ($user !== null ? $this->users->roleIdsForUser($user->id) : []);
        $errorHtml = $error !== null ? $this->notice('error', $error) : '';
        $roleOptions = $this->roleCheckboxes($selectedRoles);
        $activeSelected = $status === 'active' ? ' selected' : '';
        $disabledSelected = $status === 'disabled' ? ' selected' : '';
        $resetTwoFactorHtml = $this->resetTwoFactorPanel($user);

        $localeFieldHtml = $this->renderAdminLocaleField($submitted['locale'] ?? $user->locale ?? null);
        return <<<HTML
{$errorHtml}
<form method="post" action="{$escapedAction}" class="page-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <label>Name <input type="text" name="name" value
        ="{$name}" required></label>
    {$localeFieldHtml}
    <label>Email <input type="email" name="email" value="{$email}" required></label>
    <label>Password <input type="password" name="password" autocomplete="new-password"><span class="muted">Leave blank to keep existing password.</span></label>
    <label>Status <select name="status"><option value="active"{$activeSelected}>Active</option><option value="disabled"{$disabledSelected}>Disabled</option></select></label>
    <fieldset class="card"><legend>Roles</legend>{$roleOptions}</fieldset>
    {$resetTwoFactorHtml}
    <div class="toolbar"><button type="submit">Save user</button><a class="button secondary" href="/admin/users">Back</a></div>
</form>
HTML;
    }

    /**
     * Render the 2FA reset panel for existing users.
     */
    private function resetTwoFactorPanel(?AdminUser $user): string
    {
        if ($user === null) {
            return '';
        }

        return <<<HTML
<fieldset class="card danger-zone">
    <legend>Two-factor authentication</legend>
    <p class="muted">Reset this user's 2FA enrolment so they can set it up again on their next login. This does not display or log OTPs, TOTP secrets, recovery codes or QR data.</p>
    <button type="submit" name="_action" value="reset_2fa" class="button secondary" onclick="return confirm('Reset 2FA for this admin user? They will need to enrol again.');">Reset 2FA</button>
</fieldset>
HTML;
    }

    /**
     * Render role assignment checkboxes.
     *
     * @param list<int> $selected
     */
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

    /**
     * Extract selected role IDs from submitted form data.
     *
     * @param array<string, mixed> $form
     * @return list<int>
     */
    private function roleIdsFromForm(array $form): array
    {
        $ids = $form['role_ids'] ?? [];
        if (!is_array($ids)) {
            return [];
        }
        return array_values(array_map(static fn (mixed $id): int => (int) $id, $ids));
    }

    /**
     * Render a full admin HTML response.
     */
    private function html(string $title, string $content, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $this->guard->user(), 'admin-users'), $statusCode);
    }

    /**
     * Escape text for safe HTML output.
     */
    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    /**
     * Renders the admin interface locale field for the user form.
     *
     * This method deliberately builds escaped HTML from PHP variables instead
     * of embedding raw PHP template tags inside controller-rendered heredoc.
     */
    private function renderAdminLocaleField(?string $currentLocale): string
    {
        $currentLocale = is_string($currentLocale) ? trim($currentLocale) : '';
        $blankSelected = $currentLocale === '' ? ' selected' : '';
        $enAuSelected = $currentLocale === 'en_AU' ? ' selected' : '';

        return implode("\n", [
            '<div class="admin-form-field admin-form-field--locale">',
            '    <label for="admin-user-locale">Admin interface locale</label>',
            '    <select id="admin-user-locale" name="locale">',
            '        <option value=""' . $blankSelected . '>Use configured admin locale</option>',
            '        <option value="en_AU"' . $enAuSelected . '>English (Australia)</option>',
            '    </select>',
            '    <small class="admin-form-help">Leave blank to use the configured admin locale.</small>',
            '</div>',
        ]);
    }
}
