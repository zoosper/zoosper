<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Controller;
use Zoosper\Auth\Admin\Lifecycle\RoleLifecycleAdminResponder;

use Zoosper\Auth\Admin\Grid\AuthGridQueryState;

use Zoosper\Auth\Admin\Grid\RoleGridIndex;

use Zoosper\Core\Form\AdminFormRegistry;
use Zoosper\Core\Form\AdminFormRenderer;
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
use Zoosper\Core\Url\AdminUrlGenerator;

use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Theme\Template\TemplateRenderer;

/**
 * Admin CRUD controller for roles and permissions.
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
        private ?AdminUrlGenerator $adminUrls = null,
        private ?RoleLifecycleAdminResponder $lifecycle = null,
        private ?TemplateRenderer $templates = null,
        private ?AdminViewRendererInterface $views = null,
        private ?AdminFormRegistry $formRegistry = null,
        private ?AdminFormRenderer $formRenderer = null,
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
            'createUrl' => $this->adminUrl('roles/create'),
            'editBaseUrl' => $this->adminUrl('roles/edit'),
        ]));
    
    }

    public function createForm(Request $request): Response
    {
        $this->currentAdminUser();
        return $this->html('Create Role', $this->form($this->adminUrl('roles/create')));
    }

    public function create(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $form = $request->form();

        try {
            $id = $this->roles->createRole((string) ($form['code'] ?? ''), trim((string) ($form['label'] ?? '')), $this->idsFromForm($form, 'permission_ids'));
            $this->auditLogger?->record($actor, 'role.created', 'admin_role', (string) $id, 'Created admin role', ['code' => (string) ($form['code'] ?? '')], $request);
            return Response::redirect($this->adminUrl('roles/edit', ['id' => $id]));
        } catch (RuntimeException $exception) {
            return $this->html('Create Role', $this->form($this->adminUrl('roles/create'), null, $exception->getMessage(), $form), 422);
        }
    }

    public function editForm(Request $request): Response
    {
        $this->currentAdminUser();
        $role = $this->roleFromRequest($request);
        if ($role === null) { return $this->html('Role Not Found', '<p>Role not found.</p>', 404); }
        return $this->html('Edit Role', $this->form($this->adminUrl('roles/edit', ['id' => (int) $role['id']]), $role));
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
            return Response::redirect($this->adminUrl('roles/edit', ['id' => (int) $role['id']]));
        } catch (RuntimeException $exception) {
            return $this->html('Edit Role', $this->form($this->adminUrl('roles/edit', ['id' => (int) $role['id']]), $role, $exception->getMessage(), $form), 422);
        }
    }

    /**
     * Return the authenticated admin user after the middleware permission gate.
     */
    public function deletePermanently(\Zoosper\Core\Http\Request $request): \Zoosper\Core\Http\Response
    {
        $actor = $this->guard->user();
        $id = (int) ($request->routeParam('id') ?? $request->query('id') ?? 0);
        $role = $this->roles->findById($id);
        if ($actor === null || $role === null || $this->lifecycle === null) {
            return \Zoosper\Core\Http\Response::redirect($this->adminUrl('roles'), 303);
        }
        return $this->lifecycle->delete($id, (string) ($role['code'] ?? ''), $actor);
    }
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
        if ($this->formRegistry !== null && $this->formRenderer !== null) {
            $formDef = $this->formRegistry->get('admin.roles.form');

            $roleId = $role !== null ? (int) $role['id'] : null;
            $selectedPermissions = $submitted !== []
                ? $this->idsFromForm($submitted, 'permission_ids')
                : ($roleId !== null ? $this->roles->permissionIdsForRole($roleId) : []);
            $selectedUsers = $submitted !== []
                ? $this->idsFromForm($submitted, 'user_ids')
                : ($roleId !== null ? $this->roles->userIdsForRole($roleId) : []);

            $fields = $formDef->fields;

            // Add dynamic components as HTML fields
            $fields[] = new \Zoosper\Core\Form\AdminFormField(
                name: 'permissions',
                type: 'html',
                label: 'Permissions',
                sortOrder: 100,
                section: 'permissions',
                config: ['html' => $this->permissionTree($selectedPermissions)]
            );

            if ($this->users !== null) {
                $fields[] = new \Zoosper\Core\Form\AdminFormField(
                    name: 'users',
                    type: 'html',
                    label: 'Users',
                    sortOrder: 110,
                    section: 'users',
                    config: ['html' => $this->userAssignment($selectedUsers)]
                );
            }

            $sections = $formDef->sections;
            $sections['permissions'] = ['title' => 'Permissions', 'description' => 'Select the permissions assigned to this role.'];
            $sections['users'] = ['title' => 'User assignments', 'description' => 'Select users who should be assigned to this role.'];

            $dynamicFormDef = new \Zoosper\Core\Form\AdminFormDefinition($formDef->handle, $fields, $sections);

            $values = $submitted !== [] ? $submitted : [
                'code' => (string) ($role['code'] ?? ''),
                'label' => (string) ($role['label'] ?? ''),
            ];

            $formHtml = $this->formRenderer->render($dynamicFormDef, $values, $action, 'POST', $error ? ['_form' => $error] : [], null, $this->csrf->token());

            $lifecycleHtml = $roleId !== null && $this->lifecycle !== null
                ? $this->lifecycle->actionsHtml($roleId, (string) ($role['code'] ?? ''))
                : '';

            if ($lifecycleHtml !== '') {
                $lifecycleHtml = '<section class="admin-role-lifecycle" aria-label="Role lifecycle">' . $lifecycleHtml . '</section>';
            }

            return '
            <div class="admin-role-workspace">
                <header class="page-header admin-role-header">
                    <div class="page-header__copy">
                        <p class="page-header__eyebrow">Roles · Access control</p>
                        <h1>' . ($roleId !== null ? 'Edit role' : 'Create role') . '</h1>
                    </div>
                    <a class="button button--secondary" href="' . htmlspecialchars($this->adminUrl('roles'), ENT_QUOTES) . '">Back to roles</a>
                </header>
                ' . $formHtml . '
                ' . $lifecycleHtml . '
            </div>';
        }

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
            'lifecycleHtml' => $roleId !== null && $this->lifecycle !== null
                ? $this->lifecycle->actionsHtml($roleId, (string) ($role['code'] ?? ''))
                : '',
            'code' => (string) ($submitted['code'] ?? $role['code'] ?? ''),
            'label' => (string) ($submitted['label'] ?? $role['label'] ?? ''),
            'error' => $error,
            'permissionTree' => $this->permissionTree($selectedPermissions),
            'userAssignment' => $this->userAssignment($selectedUsers),
            'backUrl' => $this->adminUrl('roles'),
        ]);
    }

    /**
     * Load the ACL group definitions used to organise the permission tree.
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

        $assignmentUsers = method_exists($this->users, 'findForAssignmentWithSelected')
            ? $this->users->findForAssignmentWithSelected($selected)
            : $this->users->allForAssignment();

        return $this->renderRoleView('user-assignment.php', [
            'users' => $assignmentUsers,
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

    /** @param array<string, scalar|null> $query */
    private function adminUrl(string $path = '', array $query = []): string
    {
        if ($this->adminUrls !== null) {
            return $this->adminUrls->url($path, $query);
        }

        $url = $path === '' ? '/admin' : '/admin/' . ltrim($path, '/');
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return $queryString === '' ? $url : $url . '?' . $queryString;
    }

    private function html(string $title, string $content, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $this->guard->user(), 'admin-roles'), $statusCode);
    }

    private function renderRoleView(string $template, array $data = []): string
    {
        $cleanTemplate = preg_replace('/\.(latte|php)$/', '', ltrim($template, '/'));
        if ($this->templates !== null) {
            return $this->templates->render('zoosper-auth::admin/roles/' . $cleanTemplate, $data, 'default', 'admin.content');
        }

        $lattePath = dirname(__DIR__, 3) . '/resources/views/admin/roles/' . $cleanTemplate . '.latte';
        $phpPath = dirname(__DIR__, 4) . '/zoosper-admin/resources/views/admin/roles/' . $cleanTemplate . '.php';
        $path = is_file($lattePath) ? $lattePath : $phpPath;

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
