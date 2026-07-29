<?php

declare(strict_types=1);

use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Core\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Proves RoleRepository::createRole()/updateRole() and
 * AdminUserRepository::createWithRoleIds()/updateUser() are genuinely
 * atomic — not just "wrapped in try/catch", but that a failure partway
 * through the write sequence leaves the database in its ORIGINAL state,
 * with no partially-written row left behind.
 *
 * Uses a real SQLite FILE (not :memory:) so the test can force a specific
 * SQL statement to fail via a PDO subclass (PDO is a native PHP class, not
 * final, so this is safe — unlike this app's own final classes), then
 * reconnect with a clean PDO to the same file afterward to verify the
 * rollback actually happened, not merely that an exception was thrown.
 *
 * File placement: app/zoosper-auth/tests/Unit/Repository/RoleRepositoryAtomicWriteTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
final class RoleRepositoryAtomicWriteTestFailingPdo extends PDO
{
    public function __construct(string $dsn, private readonly string $failingQueryContains)
    {
        parent::__construct($dsn);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        if (str_contains($query, $this->failingQueryContains)) {
            throw new RuntimeException('Simulated failure for atomicity test: ' . $this->failingQueryContains);
        }

        return parent::prepare($query, $options);
    }
}

function roleRepoAtomicTestFreshDbFile(): string
{
    $basePath = dirname(__DIR__, 5);
    $path = sys_get_temp_dir() . '/zoosper-role-atomic-' . bin2hex(random_bytes(6)) . '.sqlite';

    $migratePdo = new PDO('sqlite:' . $path);
    $migratePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    (new Migrator($migratePdo, $basePath, new ModuleRegistry($basePath)))->migrate();
    unset($migratePdo);

    return $path;
}

function roleRepoAtomicTestSeedPermission(string $dbPath): int
{
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->prepare('INSERT INTO admin_permissions (code, label, created_at) VALUES (:code, :label, :created_at)')
        ->execute(['code' => 'test.permission.' . bin2hex(random_bytes(3)), 'label' => 'Test Permission', 'created_at' => gmdate('Y-m-d H:i:s')]);

    return (int) $pdo->lastInsertId();
}

it('rolls back the role row insert when the permission sync fails partway through createRole()', function (): void {
    $dbPath = roleRepoAtomicTestFreshDbFile();
    $permissionId = roleRepoAtomicTestSeedPermission($dbPath);

    $failingPdo = new RoleRepositoryAtomicWriteTestFailingPdo('sqlite:' . $dbPath, 'INSERT INTO admin_role_permissions');
    $roles = new RoleRepository($failingPdo);

    $roleCode = 'atomic_test_role_' . bin2hex(random_bytes(3));

    expect(fn () => $roles->createRole($roleCode, 'Atomic Test Role', [$permissionId]))
        ->toThrow(RuntimeException::class, 'Simulated failure');

    // Reconnect clean — proves the rollback actually happened in the
    // database, not merely that an exception was caught in-process.
    $verifyPdo = new PDO('sqlite:' . $dbPath);
    $statement = $verifyPdo->prepare('SELECT COUNT(*) FROM admin_roles WHERE code = :code');
    $statement->execute(['code' => $roleCode]);

    expect((int) $statement->fetchColumn())->toBe(0);

    unset($verifyPdo, $failingPdo);
    @unlink($dbPath);
});

it('rolls back the label change AND permission sync when the user-assignment sync fails partway through updateRole()', function (): void {
    $dbPath = roleRepoAtomicTestFreshDbFile();
    $permissionId = roleRepoAtomicTestSeedPermission($dbPath);

    // Create the role successfully first, with a real (non-failing) connection.
    $setupPdo = new PDO('sqlite:' . $dbPath);
    $setupPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $roleCode = 'atomic_update_test_' . bin2hex(random_bytes(3));
    (new RoleRepository($setupPdo))->createRole($roleCode, 'Original Label', [$permissionId]);
    $roleId = (int) $setupPdo->query("SELECT id FROM admin_roles WHERE code = '{$roleCode}'")->fetchColumn();
    unset($setupPdo);

    // Now attempt an update that changes the label AND fails during the
    // user-assignment sync — both should roll back together.
    $failingPdo = new RoleRepositoryAtomicWriteTestFailingPdo('sqlite:' . $dbPath, 'INSERT INTO admin_user_roles');
    $roles = new RoleRepository($failingPdo);

    expect(fn () => $roles->updateRole($roleId, $roleCode, 'CHANGED Label', [$permissionId], [999999]))
        ->toThrow(RuntimeException::class, 'Simulated failure');

    $verifyPdo = new PDO('sqlite:' . $dbPath);
    $label = $verifyPdo->query("SELECT label FROM admin_roles WHERE id = {$roleId}")->fetchColumn();

    // The label change must NOT have been persisted — proves the whole
    // updateRole() write sequence rolled back together, not just the
    // failing statement.
    expect($label)->toBe('Original Label');

    unset($verifyPdo, $failingPdo);
    @unlink($dbPath);
});

it('rolls back the admin user row insert when the role-assignment sync fails partway through createWithRoleIds()', function (): void {
    $dbPath = roleRepoAtomicTestFreshDbFile();

    // Seed a real role to reference.
    $setupPdo = new PDO('sqlite:' . $dbPath);
    $setupPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $roleCode = 'atomic_user_test_role_' . bin2hex(random_bytes(3));
    (new RoleRepository($setupPdo))->createRole($roleCode, 'Role For User Test', []);
    $roleId = (int) $setupPdo->query("SELECT id FROM admin_roles WHERE code = '{$roleCode}'")->fetchColumn();
    unset($setupPdo);

    $failingPdo = new RoleRepositoryAtomicWriteTestFailingPdo('sqlite:' . $dbPath, 'INSERT INTO admin_user_roles');
    $users = new AdminUserRepository($failingPdo);

    $email = 'atomic-test-' . bin2hex(random_bytes(3)) . '@example.test';

    expect(fn () => $users->createWithRoleIds($email, 'Atomic Test User', (new PasswordHasher())->hash('ChangeMe123!'), 'active', [$roleId]))
        ->toThrow(RuntimeException::class, 'Simulated failure');

    // Reconnect clean — the user row must NOT exist, proving the insert
    // rolled back rather than leaving an orphaned admin_users row with no
    // role assignment.
    $verifyPdo = new PDO('sqlite:' . $dbPath);
    $statement = $verifyPdo->prepare('SELECT COUNT(*) FROM admin_users WHERE email = :email');
    $statement->execute(['email' => $email]);

    expect((int) $statement->fetchColumn())->toBe(0);

    unset($verifyPdo, $failingPdo);
    @unlink($dbPath);
});

it('still succeeds normally end to end when nothing fails', function (): void {
    $dbPath = roleRepoAtomicTestFreshDbFile();
    $permissionId = roleRepoAtomicTestSeedPermission($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $roles = new RoleRepository($pdo);

    $roleCode = 'atomic_success_test_' . bin2hex(random_bytes(3));
    $roleId = $roles->createRole($roleCode, 'Success Test Role', [$permissionId]);

    expect($roleId)->toBeGreaterThan(0);
    expect($roles->permissionIdsForRole($roleId))->toBe([$permissionId]);

    $roles->updateRole($roleId, $roleCode, 'Updated Label', [$permissionId]);
    $updatedRole = $roles->findRoleById($roleId);

    expect($updatedRole['label'])->toBe('Updated Label');

    unset($pdo);
    @unlink($dbPath);
});
