<?php

declare(strict_types=1);

use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Admin\Controller\UserAdminController;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Database\Migrator;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * SECURITY REGRESSION TEST — confirms and closes a real privilege-escalation
 * vulnerability: /admin/users/edit and /admin/users/create are gated by
 * ['role.manage', 'user.manage'] with OR semantics (either permission is
 * sufficient to reach the route). UserAdminController::update()/create()
 * previously wrote submitted role_ids unconditionally, with no further check
 * on which of the two OR'd permissions the actor actually held. An admin
 * holding ONLY 'user.manage' could submit role_ids referencing the
 * super_admin role and grant it to themselves or any other user.
 *
 * UPDATED (2026-07-30, alongside the Request::form() immutability fix):
 * previously this test set global `$_POST` before calling the controller,
 * relying on Request::form() reading that superglobal directly regardless
 * of what was passed to the Request constructor. Now that form() reads from
 * an immutable constructor-provided property, form data is passed directly
 * into each `new Request(...)` call instead — no global state mutation, and
 * this also means the test no longer needs its own cleanup of $_POST
 * between cases.
 *
 * File placement: app/zoosper-auth/tests/Unit/Admin/Controller/UserAdminControllerRoleEscalationTest.php
 * — 6 levels up to repo root (one level deeper than other per-module tests
 * due to the extra Admin/Controller nesting matching the controller's own
 * namespace depth).
 */
function privescTestDatabase(): PDO
{
    $basePath = dirname(__DIR__, 6);
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    (new Migrator($pdo, $basePath, new ModuleRegistry($basePath)))->migrate();

    return $pdo;
}

/**
 * @return array{userManageRoleId: int, superAdminRoleId: int}
 */
function privescTestSeedRoles(PDO $pdo): array
{
    $roles = new RoleRepository($pdo);

    $superAdminRoleId = (int) $pdo->query("SELECT id FROM admin_roles WHERE code = 'super_admin'")->fetchColumn();

    $userManagePermissionId = (int) $pdo->query("SELECT id FROM admin_permissions WHERE code = 'user.manage'")->fetchColumn();
    $userManageRoleId = $roles->createRole('privesc_test_user_manager_' . bin2hex(random_bytes(3)), 'Test User Manager', [$userManagePermissionId]);

    return ['userManageRoleId' => $userManageRoleId, 'superAdminRoleId' => $superAdminRoleId];
}

function privescTestCreateAdmin(PDO $pdo, int $roleId, string $emailPrefix): AdminUser
{
    $users = new AdminUserRepository($pdo);
    $email = $emailPrefix . '-' . bin2hex(random_bytes(4)) . '@example.test';
    $users->createWithRoleIds($email, 'Test Admin', (new PasswordHasher())->hash('ChangeMe123!'), 'active', [$roleId]);

    return $users->findByEmail($email);
}

/**
 * AdminViewRenderer is `final readonly`, so it cannot be subclassed/faked
 * via an anonymous class the way an interface can. Since this security test
 * only cares about DATABASE state, we use
 * ReflectionClass::newInstanceWithoutConstructor() to satisfy
 * UserAdminController's type-hint with a real (but uninitialized)
 * AdminViewRenderer instance — no subclassing, no violation of `final`.
 */
function privescTestFakeViewRenderer(): AdminViewRenderer
{
    return (new ReflectionClass(AdminViewRenderer::class))->newInstanceWithoutConstructor();
}

function privescTestController(PDO $pdo, AdminUser $actor): UserAdminController
{
    $guard = new SessionGuard(new AdminUserRepository($pdo));
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $guard->login($actor);

    return new UserAdminController(
        guard: $guard,
        csrf: new CsrfTokenManager(),
        users: new AdminUserRepository($pdo),
        roles: new RoleRepository($pdo),
        passwordHasher: new PasswordHasher(),
        views: privescTestFakeViewRenderer(),
    );
}

/**
 * Run a controller action, tolerating (and discarding) any Throwable that
 * originates purely from the fake, uninitialized AdminViewRenderer being
 * asked to actually render something. Security-relevant database writes
 * happen BEFORE any render call in every path this test suite exercises, so
 * this does not mask the behaviour under test.
 */
function privescTestRunTolerant(callable $action): void
{
    try {
        $action();
    } catch (\Throwable) {
        // Intentionally discarded — see function docblock.
    }
}

it('does NOT let a user.manage-only admin escalate a target user to super_admin via update()', function (): void {
    $pdo = privescTestDatabase();
    $roleIds = privescTestSeedRoles($pdo);

    $attacker = privescTestCreateAdmin($pdo, $roleIds['userManageRoleId'], 'attacker');
    $target = privescTestCreateAdmin($pdo, $roleIds['userManageRoleId'], 'target');

    $controller = privescTestController($pdo, $attacker);

    // Form data now passed directly into the Request constructor — no
    // global $_POST mutation needed.
    $request = new Request(
        method: 'POST',
        path: '/admin/users/edit',
        query: ['id' => (string) $target->id],
        form: [
            'email' => $target->email,
            'name' => $target->name,
            'status' => 'active',
            'role_ids' => [(string) $roleIds['superAdminRoleId']], // the attack payload
        ],
    );

    privescTestRunTolerant(fn () => $controller->update($request));

    $usersAfter = new AdminUserRepository($pdo);
    $roleIdsAfter = $usersAfter->roleIdsForUser($target->id);

    expect($roleIdsAfter)->toBe([$roleIds['userManageRoleId']]);
    expect($roleIdsAfter)->not->toContain($roleIds['superAdminRoleId']);
});

it('does NOT let a user.manage-only admin create a new admin user with an attacker-chosen role', function (): void {
    $pdo = privescTestDatabase();
    $roleIds = privescTestSeedRoles($pdo);

    $attacker = privescTestCreateAdmin($pdo, $roleIds['userManageRoleId'], 'attacker2');
    $controller = privescTestController($pdo, $attacker);

    $newEmail = 'new-escalated-' . bin2hex(random_bytes(4)) . '@example.test';
    $request = new Request(
        method: 'POST',
        path: '/admin/users/create',
        form: [
            'email' => $newEmail,
            'name' => 'Escalated New Admin',
            'password' => 'ChangeMe123!ForSure',
            'status' => 'active',
            'role_ids' => [(string) $roleIds['superAdminRoleId']], // the attack payload
        ],
    );

    privescTestRunTolerant(fn () => $controller->create($request));

    $users = new AdminUserRepository($pdo);
    expect($users->findByEmail($newEmail))->toBeNull();
});

it('still allows a role.manage admin to assign roles normally via update() (no regression)', function (): void {
    $pdo = privescTestDatabase();
    $roleIds = privescTestSeedRoles($pdo);

    $roleManager = privescTestCreateAdmin($pdo, $roleIds['superAdminRoleId'], 'rolemanager');
    $target = privescTestCreateAdmin($pdo, $roleIds['userManageRoleId'], 'target2');

    $controller = privescTestController($pdo, $roleManager);

    $request = new Request(
        method: 'POST',
        path: '/admin/users/edit',
        query: ['id' => (string) $target->id],
        form: [
            'email' => $target->email,
            'name' => $target->name,
            'status' => 'active',
            'role_ids' => [(string) $roleIds['superAdminRoleId']],
        ],
    );

    privescTestRunTolerant(fn () => $controller->update($request));

    $users = new AdminUserRepository($pdo);
    expect($users->roleIdsForUser($target->id))->toBe([$roleIds['superAdminRoleId']]);
});

it('still allows a role.manage admin to create a new user with a chosen role (no regression)', function (): void {
    $pdo = privescTestDatabase();
    $roleIds = privescTestSeedRoles($pdo);

    $roleManager = privescTestCreateAdmin($pdo, $roleIds['superAdminRoleId'], 'rolemanager2');
    $controller = privescTestController($pdo, $roleManager);

    $newEmail = 'legit-new-' . bin2hex(random_bytes(4)) . '@example.test';
    $request = new Request(
        method: 'POST',
        path: '/admin/users/create',
        form: [
            'email' => $newEmail,
            'name' => 'Legit New Admin',
            'password' => 'ChangeMe123!ForSure',
            'status' => 'active',
            'role_ids' => [(string) $roleIds['userManageRoleId']],
        ],
    );

    privescTestRunTolerant(fn () => $controller->create($request));

    $users = new AdminUserRepository($pdo);
    $created = $users->findByEmail($newEmail);

    expect($created)->not->toBeNull();
    expect($users->roleIdsForUser($created->id))->toBe([$roleIds['userManageRoleId']]);
});
