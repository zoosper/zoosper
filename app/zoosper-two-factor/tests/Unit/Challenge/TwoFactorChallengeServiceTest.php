<?php

declare(strict_types=1);

use Zoosper\TwoFactor\Challenge\TwoFactorChallengeRepository;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeService;

/*
 * Phase 1.104 behavioural tests for the login-time 2FA challenge core.
 *
 * These prove the exact property Sonnet Phase 2 said had zero coverage: a
 * challenge cannot be satisfied without a valid code, and once consumed it
 * cannot be replayed. Uses a real in-memory SQLite table mirroring
 * admin_two_factor_challenges.
 */

function makeChallengePdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE admin_two_factor_challenges (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            admin_user_id INTEGER NOT NULL,
            challenge_token_hash TEXT NOT NULL,
            expires_at TEXT NOT NULL,
            consumed_at TEXT,
            created_at TEXT NOT NULL
        )'
    );
    return $pdo;
}

/** Fixed "now" so TTL behaviour is deterministic. */
function fixedNow(string $iso = '2026-07-25 08:00:00'): DateTimeImmutable
{
    return new DateTimeImmutable($iso, new DateTimeZone('UTC'));
}

it('issues a plaintext token and stores only its hash', function (): void {
    $pdo = makeChallengePdo();
    $repo = new TwoFactorChallengeRepository($pdo);
    $service = new TwoFactorChallengeService(
        $repo,
        fn (string $secret, string $code): bool => $code === '123456',
        fn (int $uid, string $code): bool => false,
        300,
        fixedNow(),
    );

    $token = $service->issue(42);

    expect($token)->toMatch('/^[a-f0-9]{64}$/');

    // The plaintext token must NOT be in the DB; only its sha256 hash.
    $row = $pdo->query('SELECT challenge_token_hash FROM admin_two_factor_challenges')->fetch(PDO::FETCH_ASSOC);
    expect($row['challenge_token_hash'])->toBe(hash('sha256', $token))
        ->and($row['challenge_token_hash'])->not->toBe($token);
});

it('passes only when the correct TOTP code is supplied', function (): void {
    $pdo = makeChallengePdo();
    $service = new TwoFactorChallengeService(
        new TwoFactorChallengeRepository($pdo),
        fn (string $secret, string $code): bool => $code === '654321',
        fn (int $uid, string $code): bool => false,
        300,
        fixedNow(),
    );

    $token = $service->issue(7);

    $wrong = $service->verifyTotp($token, '000000', 'SECRET');
    expect($wrong->passed)->toBeFalse()
        ->and($wrong->reason)->toBe('wrong_code');

    $ok = $service->verifyTotp($token, '654321', 'SECRET');
    expect($ok->passed)->toBeTrue()
        ->and($ok->adminUserId)->toBe(7)
        ->and($ok->reason)->toBe('ok');
});

it('is single-use: a consumed challenge cannot be replayed', function (): void {
    $pdo = makeChallengePdo();
    $service = new TwoFactorChallengeService(
        new TwoFactorChallengeRepository($pdo),
        fn (string $secret, string $code): bool => true,
        fn (int $uid, string $code): bool => false,
        300,
        fixedNow(),
    );

    $token = $service->issue(1);

    $first = $service->verifyTotp($token, 'anything', 'SECRET');
    expect($first->passed)->toBeTrue();

    $replay = $service->verifyTotp($token, 'anything', 'SECRET');
    expect($replay->passed)->toBeFalse()
        ->and($replay->reason)->toBe('invalid_or_expired');
});

it('rejects an expired challenge', function (): void {
    $pdo = makeChallengePdo();
    $repo = new TwoFactorChallengeRepository($pdo);

    // Issue at 08:00 with a 300s TTL...
    $issuer = new TwoFactorChallengeService($repo, fn () => true, fn () => false, 300, fixedNow('2026-07-25 08:00:00'));
    $token = $issuer->issue(9);

    // ...verify at 08:10 (past expiry).
    $verifier = new TwoFactorChallengeService($repo, fn () => true, fn () => false, 300, fixedNow('2026-07-25 08:10:00'));
    $result = $verifier->verifyTotp($token, 'anything', 'SECRET');

    expect($result->passed)->toBeFalse()
        ->and($result->reason)->toBe('invalid_or_expired');
});

it('rejects an unknown token', function (): void {
    $pdo = makeChallengePdo();
    $service = new TwoFactorChallengeService(
        new TwoFactorChallengeRepository($pdo),
        fn () => true,
        fn () => false,
        300,
        fixedNow(),
    );

    $result = $service->verifyTotp('not-a-real-token', '123456', 'SECRET');
    expect($result->passed)->toBeFalse()
        ->and($result->reason)->toBe('invalid_or_expired');
});

it('accepts a valid recovery code and consumes the challenge', function (): void {
    $pdo = makeChallengePdo();
    $redeemed = [];
    $service = new TwoFactorChallengeService(
        new TwoFactorChallengeRepository($pdo),
        fn () => false,
        function (int $uid, string $code) use (&$redeemed): bool {
            if ($code === 'RECOV-CODE-1') {
                $redeemed[] = $uid;
                return true;
            }
            return false;
        },
        300,
        fixedNow(),
    );

    $token = $service->issue(5);

    $bad = $service->verifyRecoveryCode($token, 'WRONG');
    expect($bad->passed)->toBeFalse();

    $ok = $service->verifyRecoveryCode($token, 'RECOV-CODE-1');
    expect($ok->passed)->toBeTrue()
        ->and($ok->adminUserId)->toBe(5)
        ->and($redeemed)->toBe([5]);
});

it('issuing again clears prior pending challenges for the user', function (): void {
    $pdo = makeChallengePdo();
    $repo = new TwoFactorChallengeRepository($pdo);
    $service = new TwoFactorChallengeService($repo, fn () => true, fn () => false, 300, fixedNow());

    $service->issue(3);
    $service->issue(3);

    $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_two_factor_challenges WHERE admin_user_id = 3')->fetchColumn();
    expect($count)->toBe(1);
});

it('deleteExpired removes consumed and expired rows', function (): void {
    $pdo = makeChallengePdo();
    $repo = new TwoFactorChallengeRepository($pdo);

    // consumed row
    $repo->insert(1, hash('sha256', 'a'), '2026-07-25 09:00:00', '2026-07-25 08:00:00');
    $repo->markConsumed(1, '2026-07-25 08:01:00');
    // expired row
    $repo->insert(2, hash('sha256', 'b'), '2026-07-25 07:00:00', '2026-07-25 06:55:00');
    // live row
    $repo->insert(3, hash('sha256', 'c'), '2026-07-25 09:00:00', '2026-07-25 08:00:00');

    $removed = $repo->deleteExpired('2026-07-25 08:05:00');
    expect($removed)->toBe(2);

    $remaining = (int) $pdo->query('SELECT COUNT(*) FROM admin_two_factor_challenges')->fetchColumn();
    expect($remaining)->toBe(1);
});
