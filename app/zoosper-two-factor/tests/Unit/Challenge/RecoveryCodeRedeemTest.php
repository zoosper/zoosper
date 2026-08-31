<?php

declare(strict_types=1);

use Zoosper\TwoFactor\Repository\AdminRecoveryCodeRepository;

function makeRecoveryPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_user_recovery_codes (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, code_hash TEXT NOT NULL, used_at TEXT, created_at TEXT NOT NULL DEFAULT (CURRENT_TIMESTAMP))');
    return $pdo;
}

function seedRecoveryCodes(PDO $pdo, int $userId, array $codes): void
{
    $stmt = $pdo->prepare('INSERT INTO admin_user_recovery_codes (admin_user_id, code_hash, created_at) VALUES (?, ?, ?)');
    foreach ($codes as $code) {
        $stmt->execute([$userId, password_hash($code, PASSWORD_DEFAULT), '2026-07-25 08:00:00']);
    }
}

it('redeems a valid unused recovery code exactly once', function (): void {
    $pdo = makeRecoveryPdo();
    seedRecoveryCodes($pdo, 10, ['AAAA-1111', 'BBBB-2222']);
    $repo = new AdminRecoveryCodeRepository($pdo);

    expect($repo->redeem(10, 'AAAA-1111', '2026-07-25 08:05:00'))->toBeTrue();
    expect($repo->redeem(10, 'AAAA-1111', '2026-07-25 08:06:00'))->toBeFalse();
});

it('rejects wrong or cross-user recovery codes', function (): void {
    $pdo = makeRecoveryPdo();
    seedRecoveryCodes($pdo, 11, ['CCCC-3333']);
    seedRecoveryCodes($pdo, 12, ['DDDD-4444']);
    $repo = new AdminRecoveryCodeRepository($pdo);

    expect($repo->redeem(11, 'WRONG-CODE'))->toBeFalse()
        ->and($repo->redeem(11, 'DDDD-4444'))->toBeFalse()
        ->and($repo->redeem(11, 'CCCC-3333'))->toBeTrue();
});

it('rejects an empty code without touching the store', function (): void {
    $pdo = makeRecoveryPdo();
    seedRecoveryCodes($pdo, 14, ['FFFF-6666']);
    $repo = new AdminRecoveryCodeRepository($pdo);

    expect($repo->redeem(14, '   '))->toBeFalse();
    $unused = (int) $pdo->query('SELECT COUNT(*) FROM admin_user_recovery_codes WHERE used_at IS NULL')->fetchColumn();
    expect($unused)->toBe(1);
});










