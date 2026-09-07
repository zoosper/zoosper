<?php

declare(strict_types=1);

use Zoosper\Auth\AccountLockout\AdminAccountLockoutRepository;
use Zoosper\Auth\AccountLockout\AdminAccountLockoutService;
use Zoosper\Auth\Admin\AccountLockout\AdminAccountUnlockResponder;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;

it('renders protected lockout facts and clears only lockout state', function (): void {
    $pdo = new \PDO('sqlite::memory:');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_account_lockouts (admin_user_id INTEGER PRIMARY KEY,failed_attempts INTEGER NOT NULL DEFAULT 0,locked_until TEXT NULL,last_failed_at TEXT NULL,updated_at TEXT NOT NULL)');
    $repository = new AdminAccountLockoutRepository($pdo);
    $service = new AdminAccountLockoutService($repository, 1, 900, static fn (): int => time());
    $service->recordFailure(7);
    $target = new AdminUser(7, 'target@example.test', 'Target', 'unchanged-hash', 'inactive', ['user.manage'], 'en_AU');
    $actor = new AdminUser(3, 'actor@example.test', 'Actor', 'actor-hash', 'active', ['user.manage'], 'en_AU');
    $responder = new AdminAccountUnlockResponder($service, new CsrfTokenManager());
    expect($responder->actionsHtml($target))->toContain('Failed password attempts:')->toContain('Unlock account')->toContain('/admin/users/7/unlock')->not->toContain('unchanged-hash');
    expect($responder->unlock($target, $actor)->statusCode())->toBe(303)
        ->and($repository->find(7))->toBeNull()
        ->and($target->status)->toBe('inactive')
        ->and($target->passwordHash)->toBe('unchanged-hash')
        ->and($responder->unlock($target, $actor)->statusCode())->toBe(303);
});

it('keeps unlock POST-only permission-protected and absent from public login output', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = (string) file_get_contents($root . '/app/zoosper-auth/config/admin_routes.php');
    $login = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/LoginController.php');
    expect($routes)->toContain("'method' => 'POST', 'path' => '/admin/users/{id:\\d+}/unlock'")
        ->toContain("'action' => 'unlock', 'permission' => 'user.manage'")
        ->not->toContain("'method' => 'GET', 'path' => '/admin/users/{id:\\d+}/unlock'")
        ->and($login)->not->toContain('lockedUntil')->not->toContain('failedAttempts')->not->toContain('Account locked');
});
