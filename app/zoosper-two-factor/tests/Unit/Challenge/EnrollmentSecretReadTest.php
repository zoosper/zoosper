<?php

declare(strict_types=1);

use Zoosper\TwoFactor\Repository\AdminTwoFactorEnrollmentRepository;

function makeTwoFactorPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_user_two_factor (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, method TEXT NOT NULL DEFAULT "totp", secret_ciphertext TEXT NOT NULL, enabled INTEGER NOT NULL DEFAULT 0, confirmed_at TEXT, created_at TEXT NOT NULL DEFAULT (CURRENT_TIMESTAMP), updated_at TEXT)');
    return $pdo;
}

it('returns the ciphertext only for an active enrolment', function (): void {
    $pdo = makeTwoFactorPdo();
    $pdo->prepare('INSERT INTO admin_user_two_factor (admin_user_id, method, secret_ciphertext, enabled) VALUES (?,?,?,1)')->execute([20, 'totp', 'CIPHERTEXT-XYZ']);
    $pdo->prepare('INSERT INTO admin_user_two_factor (admin_user_id, method, secret_ciphertext, enabled) VALUES (?,?,?,0)')->execute([21, 'totp', 'DISABLED-CIPHERTEXT']);

    $repo = new AdminTwoFactorEnrollmentRepository($pdo);

    expect($repo->findProtectedSecret(20))->toBe('CIPHERTEXT-XYZ')
        ->and($repo->findProtectedSecret(21))->toBeNull()
        ->and($repo->findProtectedSecret(999))->toBeNull();
});










