<?php

declare(strict_types=1);

use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\PasswordHasher;

/*
 * Phase E1 behavioural tests for AuthService's rehash-on-login upgrade path.
 * Uses a real in-memory SQLite AdminUserRepository (same fixture shape as the
 * Phase 1.109 CountingPdo tests) so the whole authenticate() -> updatePassword()
 * chain is exercised for real, not mocked.
 */

function makeAuthRehashPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL,
            name TEXT NOT NULL,
            password_hash TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT "active",
            locale TEXT,
            last_login_at TEXT,
            created_at TEXT,
            updated_at TEXT
        )'
    );
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER NOT NULL, role_id INTEGER NOT NULL)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER NOT NULL, permission_id INTEGER NOT NULL)');

    return $pdo;
}

it('transparently upgrades a weak/old password hash on a successful login', function (): void {
    $pdo = makeAuthRehashPdo();
    $hasher = new PasswordHasher();
    $weakHash = password_hash('CorrectHorseBattery1', PASSWORD_BCRYPT, ['cost' => 4]);

    $pdo->prepare('INSERT INTO admin_users (email, name, password_hash, status) VALUES (?,?,?,?)')
        ->execute(['alice@example.test', 'Alice', $weakHash, 'active']);

    $users = new AdminUserRepository($pdo);
    $auth = new AuthService($users, $hasher);

    $result = $auth->authenticate('alice@example.test', 'CorrectHorseBattery1');
    expect($result)->not->toBeNull();

    $newHash = (string) $pdo->query("SELECT password_hash FROM admin_users WHERE email = 'alice@example.test'")->fetchColumn();

    expect($newHash)->not->toBe($weakHash)
        ->and($hasher->needsRehash($newHash))->toBeFalse()
        ->and($hasher->verify('CorrectHorseBattery1', $newHash))->toBeTrue();
});

it('does NOT rehash when the submitted password is wrong', function (): void {
    $pdo = makeAuthRehashPdo();
    $hasher = new PasswordHasher();
    $weakHash = password_hash('CorrectHorseBattery1', PASSWORD_BCRYPT, ['cost' => 4]);

    $pdo->prepare('INSERT INTO admin_users (email, name, password_hash, status) VALUES (?,?,?,?)')
        ->execute(['bob@example.test', 'Bob', $weakHash, 'active']);

    $users = new AdminUserRepository($pdo);
    $auth = new AuthService($users, $hasher);

    $result = $auth->authenticate('bob@example.test', 'WrongPassword');
    expect($result)->toBeNull();

    $unchangedHash = (string) $pdo->query("SELECT password_hash FROM admin_users WHERE email = 'bob@example.test'")->fetchColumn();
    expect($unchangedHash)->toBe($weakHash);
});

it('does not touch a hash that already matches current PASSWORD_DEFAULT settings', function (): void {
    $pdo = makeAuthRehashPdo();
    $hasher = new PasswordHasher();
    $freshHash = $hasher->hash('CorrectHorseBattery1');

    $pdo->prepare('INSERT INTO admin_users (email, name, password_hash, status) VALUES (?,?,?,?)')
        ->execute(['carol@example.test', 'Carol', $freshHash, 'active']);

    $users = new AdminUserRepository($pdo);
    $auth = new AuthService($users, $hasher);

    $auth->authenticate('carol@example.test', 'CorrectHorseBattery1');

    $stillSameHash = (string) $pdo->query("SELECT password_hash FROM admin_users WHERE email = 'carol@example.test'")->fetchColumn();
    expect($stillSameHash)->toBe($freshHash);
});

it('does not rehash an inactive user even with the correct password', function (): void {
    $pdo = makeAuthRehashPdo();
    $hasher = new PasswordHasher();
    $weakHash = password_hash('CorrectHorseBattery1', PASSWORD_BCRYPT, ['cost' => 4]);

    $pdo->prepare('INSERT INTO admin_users (email, name, password_hash, status) VALUES (?,?,?,?)')
        ->execute(['dave@example.test', 'Dave', $weakHash, 'suspended']);

    $users = new AdminUserRepository($pdo);
    $auth = new AuthService($users, $hasher);

    $result = $auth->authenticate('dave@example.test', 'CorrectHorseBattery1');
    expect($result)->toBeNull();

    $unchangedHash = (string) $pdo->query("SELECT password_hash FROM admin_users WHERE email = 'dave@example.test'")->fetchColumn();
    expect($unchangedHash)->toBe($weakHash);
});










