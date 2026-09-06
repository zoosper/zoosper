<?php

declare(strict_types=1);

use Zoosper\Auth\AccountLockout\AdminAccountLockoutRepository;
use Zoosper\Auth\AccountLockout\AdminAccountLockoutService;

function c2LockoutPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_account_lockouts (admin_user_id INTEGER PRIMARY KEY, failed_attempts INTEGER NOT NULL DEFAULT 0, locked_until TEXT NULL, last_failed_at TEXT NULL, updated_at TEXT NOT NULL)');
    return $pdo;
}

it('locks exactly at the configured failed-attempt threshold', function (): void {
    $pdo = c2LockoutPdo();
    $service = new AdminAccountLockoutService(new AdminAccountLockoutRepository($pdo), 3, 600, static fn (): int => 1_800_000_000);
    expect($service->recordFailure(7)->failedAttempts)->toBe(1)
        ->and($service->isLocked(7))->toBeFalse()
        ->and($service->recordFailure(7)->failedAttempts)->toBe(2)
        ->and($service->isLocked(7))->toBeFalse();
    $locked = $service->recordFailure(7);
    expect($locked->failedAttempts)->toBe(3)
        ->and($locked->lockedUntil)->toBe('2027-01-15 08:10:00')
        ->and($service->isLocked(7))->toBeTrue();
});

it('clears expired and successful-account lockout state', function (): void {
    $pdo = c2LockoutPdo();
    $repository = new AdminAccountLockoutRepository($pdo);
    $locked = new AdminAccountLockoutService($repository, 1, 60, static fn (): int => 1_800_000_000);
    $locked->recordFailure(9);
    expect($locked->isLocked(9))->toBeTrue();
    $expired = new AdminAccountLockoutService($repository, 1, 60, static fn (): int => 1_800_000_061);
    expect($expired->isLocked(9))->toBeFalse()->and($repository->find(9))->toBeNull();
    $locked->recordFailure(9);
    $locked->clear(9);
    expect($repository->find(9))->toBeNull();
});

it('atomically increments repeated failures without duplicate rows', function (): void {
    $pdo = c2LockoutPdo();
    $repository = new AdminAccountLockoutRepository($pdo);
    for ($i = 0; $i < 8; $i++) {
        $repository->recordFailure(11, 5, '2027-01-15 08:00:00', '2027-01-15 08:15:00');
    }
    expect((int) $pdo->query('SELECT COUNT(*) FROM admin_account_lockouts')->fetchColumn())->toBe(1)
        ->and($repository->find(11)?->failedAttempts)->toBe(8)
        ->and($repository->find(11)?->lockedUntil)->toBe('2027-01-15 08:15:00');
});
