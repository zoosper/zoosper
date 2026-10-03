<?php
declare(strict_types=1);
use Zoosper\TwoFactor\Challenge\AdminTotpReplayRepository;
it('claims only strictly newer TOTP counters', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_totp_replay_state (admin_user_id INTEGER PRIMARY KEY,last_counter INTEGER NOT NULL,updated_at TEXT NOT NULL)');
    $repository = new AdminTotpReplayRepository($pdo);
    expect($repository->claimIfNewer(3, 100, '2027-01-15 08:00:00'))->toBeTrue()
        ->and($repository->claimIfNewer(3, 100, '2027-01-15 08:00:01'))->toBeFalse()
        ->and($repository->claimIfNewer(3, 99, '2027-01-15 08:00:02'))->toBeFalse()
        ->and($repository->claimIfNewer(3, 101, '2027-01-15 08:00:30'))->toBeTrue();
});
