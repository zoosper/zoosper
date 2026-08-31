<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Lifecycle;

use PDO;
use Zoosper\Auth\Entity\Save\AdminUserSaveDataFactory;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\Service\PasswordHasher;

function makeLocaleTestPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("CREATE TABLE admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        locale TEXT NULL,
        name TEXT NOT NULL,
        password_hash TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'active',
        last_login_at TEXT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )");
    $pdo->exec("CREATE TABLE admin_roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        label TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )");
    $pdo->exec("CREATE TABLE admin_user_roles (
        user_id INTEGER NOT NULL,
        role_id INTEGER NOT NULL,
        PRIMARY KEY(user_id, role_id)
    )");
    $pdo->exec("CREATE TABLE admin_permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        label TEXT NOT NULL,
        created_at TEXT NOT NULL
    )");
    $pdo->exec("CREATE TABLE admin_role_permissions (
        role_id INTEGER NOT NULL,
        permission_id INTEGER NOT NULL,
        PRIMARY KEY(role_id, permission_id)
    )");
    $pdo->exec("INSERT INTO admin_roles (code, label, created_at, updated_at) VALUES ('super_admin', 'Super Admin', '2026-01-01 00:00:00', '2026-01-01 00:00:00')");

    return $pdo;
}

it('normalises valid locale strings and rejects invalid formats via AdminUserSaveDataFactory', function (): void {
    $factory = new AdminUserSaveDataFactory();

    $valid = $factory->fromSubmitted(['name' => 'Alice', 'email' => 'alice@example.com', 'locale' => 'en_GB']);
    expect($valid->getData('locale'))->toBe('en_GB');

    $validPadded = $factory->fromSubmitted(['name' => 'Alice', 'email' => 'alice@example.com', 'locale' => '  en_AU  ']);
    expect($validPadded->getData('locale'))->toBe('en_AU');

    $empty = $factory->fromSubmitted(['name' => 'Alice', 'email' => 'alice@example.com', 'locale' => '   ']);
    expect($empty->getData('locale'))->toBeNull();

    $invalidDash = $factory->fromSubmitted(['name' => 'Alice', 'email' => 'alice@example.com', 'locale' => 'en-US']);
    expect($invalidDash->getData('locale'))->toBeNull();

    $invalidCase = $factory->fromSubmitted(['name' => 'Alice', 'email' => 'alice@example.com', 'locale' => 'EN_us']);
    expect($invalidCase->getData('locale'))->toBeNull();

    $nonString = $factory->fromSubmitted(['name' => 'Alice', 'email' => 'alice@example.com', 'locale' => ['en_US']]);
    expect($nonString->getData('locale'))->toBeNull();
});

it('persists and hydrates locale across repository operations and session authentication', function (): void {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $pdo = makeLocaleTestPdo();
    $repo = new AdminUserRepository($pdo);
    $hasher = new PasswordHasher();
    $hash = $hasher->hash('SecretPassword123!');

    $userId = $repo->createWithRoleIds('admin@example.com', 'Admin User', $hash, 'active', [1], 'en_AU');
    $user = $repo->findById($userId);

    expect($user)->not->toBeNull()
        ->and($user->locale)->toBe('en_AU')
        ->and($user->email)->toBe('admin@example.com');

    // Verify findByEmail hydration
    $byEmail = $repo->findByEmail('admin@example.com');
    expect($byEmail)->not->toBeNull()
        ->and($byEmail->locale)->toBe('en_AU');

    // Update to different locale
    $repo->updateUser($userId, 'admin@example.com', 'Admin User Updated', 'active', [1], 'fr_FR');
    $updatedUser = $repo->findById($userId);
    expect($updatedUser?->locale)->toBe('fr_FR')
        ->and($updatedUser?->name)->toBe('Admin User Updated');

    // Clear locale to null
    $repo->updateUser($userId, 'admin@example.com', 'Admin User Updated', 'active', [1], null);
    $clearedUser = $repo->findById($userId);
    expect($clearedUser?->locale)->toBeNull();

    // Verify SessionGuard integration
    $repo->updateUser($userId, 'admin@example.com', 'Admin User', 'active', [1], 'en_GB');
    $guard = new SessionGuard($repo);
    $authenticatedUser = $repo->findById($userId);
    $guard->login($authenticatedUser);

    expect($guard->user())->not->toBeNull()
        ->and($guard->user()?->locale)->toBe('en_GB');
});










