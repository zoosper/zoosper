<?php

declare(strict_types=1);

use Zoosper\Admin\Message\SessionFlashMessageStore;
use Zoosper\Auth\Admin\Lifecycle\RoleLifecycleAdminResponder;
use Zoosper\Auth\Lifecycle\AdminUserLifecycleCoordinator;
use Zoosper\Auth\Lifecycle\RoleLifecycleCoordinator;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;

function identityLifecyclePdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    (new Migrator($pdo, dirname(__DIR__, 5), new ModuleRegistry(dirname(__DIR__, 5))))->migrate();
    return $pdo;
}

it('blocks self-disable and the last active super administrator', function (): void {
    $pdo = identityLifecyclePdo();
    $users = new AdminUserRepository($pdo);
    $roleId = (int) $pdo->query("SELECT id FROM admin_roles WHERE code='super_admin'")->fetchColumn();
    $id = $users->createWithRoleIds('only@example.test', 'Only', (new PasswordHasher())->hash('ChangeMe123!'), 'active', [$roleId]);
    $user = $users->findById($id);
    $life = new AdminUserLifecycleCoordinator($pdo, $users);
    expect($life->disable($user, $user)->successful)->toBeFalse();
    $actorRoleId = (new RoleRepository($pdo))->createRole('lifecycle_actor', 'Lifecycle Actor', []);
    $secondId = $users->createWithRoleIds('actor@example.test', 'Actor', (new PasswordHasher())->hash('ChangeMe123!'), 'active', [$actorRoleId]);
    expect($life->disable($user, $users->findById($secondId))->successful)->toBeFalse();
});

it('disables and restores a non-current Admin User without deleting identity', function (): void {
    $pdo = identityLifecyclePdo(); $users = new AdminUserRepository($pdo); $hash = (new PasswordHasher())->hash('ChangeMe123!');
    $roleId = (new RoleRepository($pdo))->createRole('lifecycle_standard', 'Lifecycle Standard', []);
    $actorId = $users->createWithRoleIds('actor@example.test', 'Actor', $hash, 'active', [$roleId]);
    $targetId = $users->createWithRoleIds('target@example.test', 'Target', $hash, 'active', [$roleId]);
    $life = new AdminUserLifecycleCoordinator($pdo, $users);
    expect($life->disable($users->findById($targetId), $users->findById($actorId))->successful)->toBeTrue()
        ->and($users->findById($targetId)->status)->toBe('inactive')
        ->and($life->restore($users->findById($targetId), $users->findById($actorId))->successful)->toBeTrue()
        ->and($users->findById($targetId)->status)->toBe('active');
});

it('protects system and assigned Roles and deletes an unassigned custom Role', function (): void {
    $pdo = identityLifecyclePdo(); $roles = new RoleRepository($pdo); $users = new AdminUserRepository($pdo); $hash = (new PasswordHasher())->hash('ChangeMe123!');
    $actorRoleId = $roles->createRole('role_lifecycle_actor', 'Role Lifecycle Actor', []);
    $actorId = $users->createWithRoleIds('actor@example.test', 'Actor', $hash, 'active', [$actorRoleId]); $actor = $users->findById($actorId);
    $systemId = (int) $pdo->query("SELECT id FROM admin_roles WHERE code='super_admin'")->fetchColumn();
    $life = new RoleLifecycleCoordinator($pdo);
    expect($life->deletePermanently($systemId, 'super_admin', $actor)->successful)->toBeFalse();
    $assignedId = $roles->createRole('assigned_test', 'Assigned Test', []); $users->createWithRoleIds('assigned@example.test', 'Assigned', $hash, 'active', [$assignedId]);
    expect($life->deletePermanently($assignedId, 'assigned_test', $actor)->successful)->toBeFalse();
    $freeId = $roles->createRole('free_test', 'Free Test', []);
    expect($life->deletePermanently($freeId, 'free_test', $actor)->successful)->toBeTrue();
    $deleted = $pdo->prepare('SELECT COUNT(*) FROM admin_roles WHERE id = :id');
    $deleted->execute(['id' => $freeId]);
    expect((int) $deleted->fetchColumn())->toBe(0);
});

it('reports the current assignment count when role deletion is denied', function (): void {
    $previousSession = $_SESSION ?? [];
    $_SESSION = [];
    $pdo = identityLifecyclePdo();
    $roles = new RoleRepository($pdo);
    $users = new AdminUserRepository($pdo);
    $roleId = $roles->createRole('assigned_response', 'Assigned Response', []);
    $hash = (new PasswordHasher())->hash('ChangeMe123!');
    $actorId = $users->createWithRoleIds('first@example.test', 'First', $hash, 'active', [$roleId]);
    $users->createWithRoleIds('second@example.test', 'Second', $hash, 'active', [$roleId]);
    $flash = new SessionFlashMessageStore();
    $responder = new RoleLifecycleAdminResponder(new RoleLifecycleCoordinator($pdo), new CsrfTokenManager(), $flash);

    $responder->delete($roleId, 'assigned_response', $users->findById($actorId));

    expect($flash->peek()[0]->text)->toContain('2 Admin User(s)')
        ->and($roles->findRoleByCode('assigned_response'))->not->toBeNull();

    $flash->pull();
    $responder->delete($roleId, 'super_admin', $users->findById($actorId));
    expect($flash->peek()[0]->text)->toBe('System Roles cannot be deleted.');

    $flash->pull();
    $freeId = $roles->createRole('free_response', 'Free Response', []);
    $responder->delete($freeId, 'free_response', $users->findById($actorId));
    expect($flash->peek()[0]->text)->toBe('Role permanently deleted.')
        ->and($roles->findRoleByCode('free_response'))->toBeNull();
    $_SESSION = $previousSession;
});










