<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\PasswordReset;

use PDO;
use Zoosper\Auth\PasswordReset\AdminPasswordResetService;
use Zoosper\Auth\PasswordReset\AdminPasswordResetTokenRepository;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Security\PasswordPolicy;
use Zoosper\Auth\Service\PasswordHasher;

function phase13C1B1Pdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT,email TEXT UNIQUE NOT NULL,name TEXT NOT NULL,password_hash TEXT NOT NULL,status TEXT NOT NULL,locale TEXT NULL,created_at TEXT NULL,updated_at TEXT NULL)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER NOT NULL,role_id INTEGER NOT NULL)');
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY,code TEXT,name TEXT)');
    $pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER,permission_id INTEGER)');
    $pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY,code TEXT,name TEXT)');
    $pdo->exec('CREATE TABLE admin_password_reset_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT,public_id TEXT UNIQUE NOT NULL,admin_user_id INTEGER NOT NULL,token_hash TEXT NOT NULL,expires_at TEXT NOT NULL,consumed_at TEXT NULL,created_at TEXT NOT NULL)');
    $pdo->exec("INSERT INTO admin_roles(id,code,name) VALUES (1,'super_admin','Super Admin')");
    return $pdo;
}

function phase13C1B1Service(PDO $pdo, int $now = 1_800_000_000): AdminPasswordResetService
{
    return new AdminPasswordResetService(
        $pdo,
        new AdminUserRepository($pdo),
        new AdminPasswordResetTokenRepository($pdo),
        new PasswordHasher(),
        new PasswordPolicy(),
        3600,
        static fn (): int => $now,
    );
}

it('issues a single-use reset token while persisting only its hash', function (): void {
    $pdo = phase13C1B1Pdo();
    $users = new AdminUserRepository($pdo);
    $id = $users->createWithRoleIds('admin@example.test', 'Admin', (new PasswordHasher())->hash('InitialPassword123!'), 'active', [1]);
    $issue = phase13C1B1Service($pdo)->issueForEmail('ADMIN@example.test');
    $row = $pdo->query('SELECT * FROM admin_password_reset_tokens')->fetch(PDO::FETCH_ASSOC);

    expect($issue)->not->toBeNull()
        ->and($issue->adminUserId)->toBe($id)
        ->and($issue->token)->toMatch('/^zp_reset_[a-f0-9]{16}_[a-f0-9]{64}$/D')
        ->and($row['token_hash'])->toBe(hash('sha256', $issue->token))
        ->and(implode(' ', $row))->not->toContain($issue->token);
});

it('does not issue a token for unknown or inactive accounts', function (): void {
    $pdo = phase13C1B1Pdo();
    $users = new AdminUserRepository($pdo);
    $users->createWithRoleIds('inactive@example.test', 'Inactive', (new PasswordHasher())->hash('InitialPassword123!'), 'inactive', [1]);
    $service = phase13C1B1Service($pdo);
    expect($service->issueForEmail('missing@example.test'))->toBeNull()
        ->and($service->issueForEmail('inactive@example.test'))->toBeNull()
        ->and((int) $pdo->query('SELECT COUNT(*) FROM admin_password_reset_tokens')->fetchColumn())->toBe(0);
});

it('resets the password once and invalidates reuse', function (): void {
    $pdo = phase13C1B1Pdo();
    $users = new AdminUserRepository($pdo);
    $id = $users->createWithRoleIds('admin@example.test', 'Admin', (new PasswordHasher())->hash('InitialPassword123!'), 'active', [1]);
    $service = phase13C1B1Service($pdo);
    $issue = $service->issueForEmail('admin@example.test');
    expect($issue)->not->toBeNull()
        ->and($service->reset($issue->token, 'ReplacementPassword123!', 'ReplacementPassword123!'))->toBe([])
        ->and((new PasswordHasher())->verify('ReplacementPassword123!', $users->findById($id)->passwordHash))->toBeTrue()
        ->and($service->reset($issue->token, 'AnotherPassword123!', 'AnotherPassword123!'))->toBe(['This password reset link is invalid or has expired.']);
});

it('rejects malformed expired mismatched and weak reset attempts', function (): void {
    $pdo = phase13C1B1Pdo();
    $users = new AdminUserRepository($pdo);
    $users->createWithRoleIds('admin@example.test', 'Admin', (new PasswordHasher())->hash('InitialPassword123!'), 'active', [1]);
    $service = phase13C1B1Service($pdo);
    $issue = $service->issueForEmail('admin@example.test');
    expect($service->reset('bad', 'ReplacementPassword123!', 'ReplacementPassword123!'))->toBe(['This password reset link is invalid or has expired.'])
        ->and($service->reset($issue->token, 'one', 'two'))->toBe(['Password confirmation does not match.'])
        ->and($service->reset($issue->token, 'short', 'short'))->not->toBe([])
        ->and(phase13C1B1Service($pdo, 1_800_004_000)->reset($issue->token, 'ReplacementPassword123!', 'ReplacementPassword123!'))->toBe(['This password reset link is invalid or has expired.']);
});
