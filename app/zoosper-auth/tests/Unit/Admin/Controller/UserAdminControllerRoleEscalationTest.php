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
 * vulnerability (found via reviewer pass, confirmed 2026-07-29 by reading the
 * actual controller and route config together):
 *
 * /admin/users/edit and /admin/users/create are gated by
 * ['role.manage', 'user.manage'] with OR semantics (either permission is
 * sufficient to reach the route — see app/zoosper-auth/config/admin_routes.php).
 * UserAdminController::update()/create() previously wrote submitted role_ids
 * unconditionally, with no further check on which of the two OR'd
 * permissions the actor actually held. An admin holding ONLY 'user.manage'
 * could submit role_ids referencing the super_admin role and grant it to
 * themselves or any other user.
 *
 * This test proves, against REAL instances (SessionGuard, AdminUserRepository,
 * RoleRepository, AdminUser are all `final` and cannot be mocked/subclassed —
 * same approach as AdminCreateCommandTest and the media upload tests), that:
 * 1. A user.manage-only admin CANNOT escalate a target user's roles via
 *    update() — the target's original roles are preserved untouched.
 * 2. A user.manage-only admin CANNOT create a new admin user with an
 *    attacker-chosen role at all — create() fails closed with a clear error.
 * 3. A role.manage admin's legitimate ability to manage roles via both
 *    create() and update() is completely unaffected by this fix.
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
 * Seed two roles: one with only 'user.manage' (the attacker's own role,
 * mirroring the real separation-of-duties intent), and confirm/find the
 * super_admin role already seeded by the auth migrations.
 *
 * @return array{userManageRoleId: int, superAdminRoleId: int}
 */
function privescTestSeedRoles(PDO $pdo): array
{
    $roles = new RoleRepository($pdo);

    // Find the super_admin role id (seeded by the real auth migrations —
    // confirmed present in this codebase's own migration/seed files).
    $superAdminRoleId = (int) $pdo->query("SELECT id FROM admin_roles WHERE code = 'super_admin'")->fetchColumn();

    // Find or create a permission-scoped role holding ONLY user.manage —
    // this represents the legitimate "user manager, not role manager"
    // account the separation-of-duties design intends to support.
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
 * only cares about DATABASE state (was a role escalation write blocked?),
 * not about HTML rendering, we use ReflectionClass::newInstanceWithoutConstructor()
 * to satisfy UserAdminController's type-hint with a real (but uninitialized)
 * AdminViewRenderer instance — no subclassing, no violation of `final`.
 *
 * If ->render() is ever actually invoked on this instance (it is, on
 * create()'s fail-closed error-rendering path), accessing its uninitialized
 * typed properties throws a plain Error. Every test below wraps its
 * controller call in try/catch(Throwable) specifically to tolerate this —
 * we only assert on database state afterward, never on the rendered HTML.
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
 * this does not mask the behaviour under test — it only prevents an
 * unrelated rendering failure from failing the test before we get to check
 * the database.
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

    // The attacker: an admin holding ONLY user.manage.
    $attacker = privescTestCreateAdmin($pdo, $roleIds['userManageRoleId'], 'attacker');

    // The target: some other ordinary admin user, currently holding the
    // same low-privilege role as the attacker (not super_admin).
    $target = privescTestCreateAdmin($pdo, $roleIds['userManageRoleId'], 'target');

    $controller = privescTestController($pdo, $attacker);

    $_POST = [
        'email' => $target->email,
        'name' => $target->name,
        'status' => 'active',
        'role_ids' => [(string) $roleIds['superAdminRoleId']], // the attack payload
    ];

    $request = new Request(method: 'POST', path: '/admin/users/edit', query: ['id' => (string) $target->id]);
    privescTestRunTolerant(fn () => $controller->update($request));

    // The critical assertion: the target user's roles must be UNCHANGED —
    // still the low-privilege role, NOT super_admin.
    $usersAfter = new AdminUserRepository($pdo);
    $roleIdsAfter = $usersAfter->roleIdsForUser($target->id);

    expect($roleIdsAfter)->toBe([$roleIds['userManageRoleId']]);
    expect($roleIdsAfter)->not->toContain($roleIds['superAdminRoleId']);

    $_POST = [];
});

it('does NOT let a user.manage-only admin create a new admin user with an attacker-chosen role', function (): void {
    $pdo = privescTestDatabase();
    $roleIds = privescTestSeedRoles($pdo);

    $attacker = privescTestCreateAdmin($pdo, $roleIds['userManageRoleId'], 'attacker2');
    $controller = privescTestController($pdo, $attacker);

    $newEmail = 'new-escalated-' . bin2hex(random_bytes(4)) . '@example.test';
    $_POST = [
        'email' => $newEmail,
        'name' => 'Escalated New Admin',
        'password' => 'ChangeMe123!ForSure',
        'status' => 'active',
        'role_ids' => [(string) $roleIds['superAdminRoleId']], // the attack payload
    ];

    $request = new Request(method: 'POST', path: '/admin/users/create');
    privescTestRunTolerant(fn () => $controller->create($request));

    // The critical assertion: no such user was ever created at all — the
    // create must fail closed, not silently succeed with a lesser role.
    $users = new AdminUserRepository($pdo);
    expect($users->findByEmail($newEmail))->toBeNull();

    $_POST = [];
});

it('still allows a role.manage admin to assign roles normally via update() (no regression)', function (): void {
    $pdo = privescTestDatabase();
    $roleIds = privescTestSeedRoles($pdo);

    // A legitimate role manager, holding the real super_admin role (which
    // includes role.manage per the seeded permission set).
    $roleManager = privescTestCreateAdmin($pdo, $roleIds['superAdminRoleId'], 'rolemanager');
    $target = privescTestCreateAdmin($pdo, $roleIds['userManageRoleId'], 'target2');

    $controller = privescTestController($pdo, $roleManager);

    $_POST = [
        'email' => $target->email,
        'name' => $target->name,
        'status' => 'active',
        'role_ids' => [(string) $roleIds['superAdminRoleId']],
    ];

    $request = new Request(method: 'POST', path: '/admin/users/edit', query: ['id' => (string) $target->id]);
    privescTestRunTolerant(fn () => $controller->update($request));

    // A legitimate role.manage admin's role change MUST still work.
    $users = new AdminUserRepository($pdo);
    expect($users->roleIdsForUser($target->id))->toBe([$roleIds['superAdminRoleId']]);

    $_POST = [];
});

it('still allows a role.manage admin to create a new user with a chosen role (no regression)', function (): void {
    $pdo = privescTestDatabase();
    $roleIds = privescTestSeedRoles($pdo);

    $roleManager = privescTestCreateAdmin($pdo, $roleIds['superAdminRoleId'], 'rolemanager2');
    $controller = privescTestController($pdo, $roleManager);

    $newEmail = 'legit-new-' . bin2hex(random_bytes(4)) . '@example.test';
    $_POST = [
        'email' => $newEmail,
        'name' => 'Legit New Admin',
        'password' => 'ChangeMe123!ForSure',
        'status' => 'active',
        'role_ids' => [(string) $roleIds['userManageRoleId']],
    ];

    $request = new Request(method: 'POST', path: '/admin/users/create');
    privescTestRunTolerant(fn () => $controller->create($request));

    $users = new AdminUserRepository($pdo);
    $created = $users->findByEmail($newEmail);

    expect($created)->not->toBeNull();
    expect($users->roleIdsForUser($created->id))->toBe([$roleIds['userManageRoleId']]);

    $_POST = [];
});
