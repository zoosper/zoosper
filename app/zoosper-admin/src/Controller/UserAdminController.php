<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\TwoFactor\Service\AdminTwoFactorResetService;

/**
 * Admin CRUD controller for admin users.
 *
 * Phase 1.26a: this is now a thin request handler. It resolves permission and
 * CSRF, reads the request, calls services, builds a small view-model, and renders
 * a Latte template. ALL HTML lives in templates under
 * app/zoosper-auth/resources/views/admin/users/ (namespace zoosper-auth::).
 * Admin-user saves run through the entity save lifecycle when a runner is injected.
 *
 * PCI-aware: the 2FA reset action never reads, displays or logs OTPs, TOTP
 * secrets, recovery-code plaintext, provisioning URIs, QR data, SMTP passwords
 * or reset tokens.
 */
final readonly class UserAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminUserRepository $users,
        private RoleRepository $roles,
        private PasswordHasher $passwordHasher,
        private AdminViewRenderer $views,
        private ?AdminTwoFactorResetService $twoFactorReset = null,
        private ?EntitySaveLifecycleRunner $saveLifecycle = null,
    ) {
    }

    public function index(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        return Response::html($this->views->render(
            'Admin Users',
            'zoosper-auth::admin/users/index',
            ['users' => $this->users->all()],
            $this->guard->user(),
            'admin-users',
        ));
    }

    public function createForm(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        return $this->renderUserForm('Create Admin User', '/admin/users/create', null, []);
    }

    public function create(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        $form = $request->form();

        try {
            $password = (string) ($form['password'] ?? '');
            if ($password === '') {
                throw new RuntimeException('Password is required for new admin users.');
            }

            $createdId = null;
            $context = $this->runEntitySave('admin_user', $form, null, function (EntitySaveContext $c) use ($form, $password, &$createdId): void {
                $createdId = $this->users->createWithRoleIds(
                    email: trim((string) ($form['email'] ?? '')),
                    name: trim((string) ($form['name'] ?? '')),
                    hash: $this->passwordHasher->hash($password),
                    status: (string) ($form['status'] ?? 'active'),
                    roleIds: $this->roleIdsFromForm($form),
                    locale: $this->adminUserLocaleFromForm($form));
            });

            if ($context->hasErrors()) {
                return $this->renderUserForm('Create Admin User', '/admin/users/create', null, $form, 422, $this->firstContextError($context));
            }

            return Response::redirect('/admin/users/edit?id=' . $createdId . '&notice=created');
        } catch (RuntimeException $exception) {
            return $this->renderUserForm('Create Admin User', '/admin/users/create', null, $form, 422, $exception->getMessage());
        }
    }

    public function editForm(Request $request): Response
    {
        if ($this->requireUserManager() === null) {
            return Response::redirect('/admin/login');
        }

        $user = $this->userFromRequest($request);
        if ($user === null) {
            return $this->renderMessage('Admin User Not Found', 'Admin user not found.', 404);
        }

        [$noticeType, $noticeMessage] = $this->noticeFor($request);

        return $this->renderUserForm('Edit Admin User', '/admin/users/edit?id=' . $user->id, $user, [], 200, null, $noticeType, $noticeMessage);
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
            return $this->renderMessage('Admin User Not Found', 'Admin user not found.', 404);
        }

        $form = $request->form();

        if (($form['_action'] ?? '') === 'reset_2fa') {
            return $this->resetTwoFactor($user, $actor);
        }

        try {
            $context = $this->runEntitySave('admin_user', $form, $user->id, function (EntitySaveContext $c) use ($form, $user): void {
                $this->users->updateUser(
                    id: $user->id,
                    email: trim((string) ($form['email'] ?? '')),
                    name: trim((string) ($form['name'] ?? '')),
                    status: (string) ($form['status'] ?? 'active'),
                    roleIds: $this->roleIdsFromForm($form),
                    locale: $this->adminUserLocaleFromForm($form));

                $password = trim((string) ($form['password'] ?? ''));
                if ($password !== '') {
                    $this->users->updatePassword($user->id, $this->passwordHasher->hash($password));
                }
            });

            if ($context->hasErrors()) {
                return $this->renderUserForm('Edit Admin User', '/admin/users/edit?id=' . $user->id, $user, $form, 422, $this->firstContextError($context));
            }

            return Response::redirect('/admin/users/edit?id=' . $user->id . '&notice=saved');
        } catch (RuntimeException $exception) {
            return $this->renderUserForm('Edit Admin User', '/admin/users/edit?id=' . $user->id, $user, $form, 422, $exception->getMessage());
        }
    }

    /**
     * Reset a user's 2FA state so they can enrol again.
     *
     * PCI-aware: never reads, displays or logs secrets, TOTP data or tokens.
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
     * Run a persistence closure through the entity save lifecycle when a runner
     * is injected, falling back to a direct save when it is not.
     *
     * @param array<string, mixed>              $form
     * @param callable(EntitySaveContext): void $save
     */
    private function runEntitySave(string $entityType, array $form, int|string|null $entityId, callable $save): EntitySaveContext
    {
        $data = (new EntityDataObject())->addData($form);
        $context = new EntitySaveContext($entityType, $data, new FieldDefinitionRegistry(), $entityId);

        if ($this->saveLifecycle !== null) {
            return $this->saveLifecycle->run($context, $save);
        }

        $save($context);

        return $context;
    }

    /**
     * Flatten accumulated lifecycle errors into a single message string.
     */
    private function firstContextError(EntitySaveContext $context): string
    {
        $messages = [];
        foreach ($context->errors() as $fieldErrors) {
            foreach ($fieldErrors as $message) {
                $messages[] = (string) $message;
            }
        }

        return $messages === [] ? 'Please review the form.' : implode(' ', $messages);
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
     * Map a query-string notice code to a [type, message] pair for the template.
     *
     * @return array{0: string|null, 1: string}
     */
    private function noticeFor(Request $request): array
    {
        return match ($request->query('notice')) {
            'created' => ['success', 'Admin user created.'],
            'saved' => ['success', 'Admin user saved.'],
            '2fa_reset' => ['success', '2FA reset completed. The admin user can enrol again on their next login.'],
            '2fa_unavailable' => ['error', '2FA reset service is not available.'],
            '2fa_failed' => ['error', '2FA reset failed. Check application logs for non-sensitive error details.'],
            default => [null, ''],
        };
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
     * Normalise the submitted admin interface locale through the AdminUser save
     * data factory, keeping controller locale handling aligned with the modular
     * field-definition write map.
     *
     * @param array<string, mixed> $form
     */
    private function adminUserLocaleFromForm(array $form): ?string
    {
        $data = (new \Zoosper\Auth\Entity\Save\AdminUserSaveDataFactory())->fromSubmitted($form);
        $locale = $data->getData('locale');

        return is_string($locale) && trim($locale) !== '' ? trim($locale) : null;
    }

    /**
     * Build the create/edit form view-model and render the Latte template.
     *
     * @param array<string, mixed> $submitted
     */
    private function renderUserForm(
        string $title,
        string $action,
        ?AdminUser $user,
        array $submitted,
        int $status = 200,
        ?string $error = null,
        ?string $noticeType = null,
        string $noticeMessage = '',
    ): Response {
        $selectedRoleIds = $submitted !== []
            ? $this->roleIdsFromForm($submitted)
            : ($user !== null ? $this->users->roleIdsForUser($user->id) : []);

        $roles = [];
        foreach ($this->roles->allRoles() as $role) {
            $id = (int) $role['id'];
            $roles[] = [
                'id' => $id,
                'label' => (string) $role['label'],
                'checked' => in_array($id, $selectedRoleIds, true),
            ];
        }

        $currentLocale = trim((string) ($submitted['locale'] ?? $user?->locale ?? ''));

        $viewModel = [
            'action' => $action,
            'csrfToken' => $this->csrf->token(),
            'name' => (string) ($submitted['name'] ?? $user?->name ?? ''),
            'email' => (string) ($submitted['email'] ?? $user?->email ?? ''),
            'status' => (string) ($submitted['status'] ?? $user?->status ?? 'active'),
            'currentLocale' => $currentLocale,
            'roles' => $roles,
            'isEdit' => $user !== null,
            'error' => $error,
            'noticeType' => $noticeType,
            'noticeMessage' => $noticeMessage,
        ];

        return Response::html($this->views->render(
            $title,
            'zoosper-auth::admin/users/form',
            $viewModel,
            $this->guard->user(),
            'admin-users',
        ), $status);
    }

    /**
     * Render a simple admin message screen (e.g. not-found).
     */
    private function renderMessage(string $title, string $message, int $status): Response
    {
        return Response::html($this->views->render(
            $title,
            'zoosper-auth::admin/users/message',
            ['message' => $message],
            $this->guard->user(),
            'admin-users',
        ), $status);
    }
}
