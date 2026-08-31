<?php

declare(strict_types=1);

use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\SessionGuard;

beforeEach(function (): void {
    $_SESSION = [];
});

afterEach(function (): void {
    $_SESSION = [];
});

/** @return array{SessionGuard, Closure(): bool, Closure(): void} */
function timeoutGuard(int $timeout, int &$now): array
{
    $guard = new SessionGuard(
        new AdminUserRepository(new PDO('sqlite::memory:')),
        $timeout,
        static function () use (&$now): int { return $now; },
    );
    $reflection = new ReflectionClass($guard);
    $expire = $reflection->getMethod('expireIfIdle');
    $touch = $reflection->getMethod('touch');

    return [
        $guard,
        static fn (): bool => (bool) $expire->invoke($guard),
        static function () use ($touch, $guard): void { $touch->invoke($guard); },
    ];
}

it('keeps protected state valid at the exact idle boundary and expires it immediately after', function (): void {
    $now = 100;
    [, $expire] = timeoutGuard(10, $now);
    $_SESSION = ['pending_2fa_user_id' => 7, 'admin_last_activity_at' => 90];

    expect($expire())->toBeFalse()
        ->and($_SESSION['pending_2fa_user_id'])->toBe(7);

    $now = 101;
    expect($expire())->toBeTrue()
        ->and($_SESSION)->not->toHaveKey('pending_2fa_user_id')
        ->not->toHaveKey('admin_last_activity_at');
});

it('refreshes valid pending two-factor activity using the deterministic clock', function (): void {
    $now = 100;
    [$guard] = timeoutGuard(10, $now);
    $_SESSION = ['pending_2fa_user_id' => 9, 'admin_last_activity_at' => 95];

    expect($guard->pendingTwoFactorUserId())->toBe(9)
        ->and($_SESSION['admin_last_activity_at'])->toBe(100);

    $now = 110;
    expect($guard->pendingTwoFactorUserId())->toBe(9)
        ->and($_SESSION['admin_last_activity_at'])->toBe(110);
});

it('fails closed for missing malformed or future activity timestamps', function (mixed $activity): void {
    $now = 100;
    [, $expire] = timeoutGuard(10, $now);
    $_SESSION = ['admin_user_id' => 3];
    if ($activity !== null) {
        $_SESSION['admin_last_activity_at'] = $activity;
    }

    expect($expire())->toBeTrue()
        ->and($_SESSION)->not->toHaveKey('admin_user_id')
        ->not->toHaveKey('admin_last_activity_at');
})->with([null, 'invalid', 101]);

it('allows timeout zero to disable expiry without mutating protected state', function (): void {
    $now = 1000;
    [, $expire] = timeoutGuard(0, $now);
    $_SESSION = ['pending_2fa_user_id' => 5, 'admin_last_activity_at' => 1];

    expect($expire())->toBeFalse()
        ->and($_SESSION['pending_2fa_user_id'])->toBe(5)
        ->and($_SESSION['admin_last_activity_at'])->toBe(1);
});

it('removes orphaned activity state when no authenticated or pending identity exists', function (): void {
    $now = 100;
    [, $expire] = timeoutGuard(10, $now);
    $_SESSION = ['admin_last_activity_at' => 99];

    expect($expire())->toBeFalse()
        ->and($_SESSION)->not->toHaveKey('admin_last_activity_at');
});

it('clears activity when a standalone pending challenge is explicitly cleared', function (): void {
    $now = 100;
    [$guard] = timeoutGuard(10, $now);
    $_SESSION = ['pending_2fa_user_id' => 7, 'admin_last_activity_at' => 99];

    $guard->clearPendingTwoFactorChallenge();

    expect($_SESSION)->not->toHaveKey('pending_2fa_user_id')
        ->not->toHaveKey('admin_last_activity_at');
});

it('clears per-request memoized user state on reset', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password_hash TEXT, status TEXT, is_active INTEGER, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER, role_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER, permission_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY, code TEXT, name TEXT, group_name TEXT)');
    $pdo->exec("INSERT INTO admin_users (id, name, email, password_hash, status, is_active) VALUES (1, 'Admin', 'admin@example.com', '', 'active', 1)");

    $users = new AdminUserRepository($pdo);
    $guard = new SessionGuard($users, 7200);

    $_SESSION['admin_user_id'] = 1;
    $_SESSION['admin_last_activity_at'] = time();

    $user1 = $guard->user();
    expect($user1)->not->toBeNull()
        ->and($user1->name)->toBe('Admin');

    $pdo->exec("UPDATE admin_users SET name = 'Updated Admin' WHERE id = 1");

    // Memoized result before reset
    expect($guard->user()->name)->toBe('Admin');

    $guard->reset();

    // Re-queried result after reset
    expect($guard->user()->name)->toBe('Updated Admin');
});










