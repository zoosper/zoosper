<?php

declare(strict_types=1);

use Zoosper\TwoFactor\Challenge\AdminTotpReplayRepository;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeRepository;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeService;
use Zoosper\TwoFactor\Repository\AdminRecoveryCodeRepository;

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
        fn (int $uid, string $secret, string $code): bool => true,
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

it('requires replay claiming for every TOTP challenge service', function (): void {
    $constructor = new ReflectionMethod(TwoFactorChallengeService::class, '__construct');
    $parameter = array_values(array_filter(
        $constructor->getParameters(),
        static fn (ReflectionParameter $parameter): bool => $parameter->getName() === 'claimTotpUse',
    ))[0] ?? null;

    expect($parameter)->not->toBeNull()
        ->and($parameter?->isOptional())->toBeFalse()
        ->and($parameter?->allowsNull())->toBeFalse();
});

it('passes only when the correct TOTP code is supplied', function (): void {
    $pdo = makeChallengePdo();
    $service = new TwoFactorChallengeService(
        new TwoFactorChallengeRepository($pdo),
        fn (string $secret, string $code): bool => $code === '654321',
        fn (int $uid, string $code): bool => false,
        fn (int $uid, string $secret, string $code): bool => $code === '654321',
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
        fn (int $uid, string $secret, string $code): bool => true,
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

it('rolls back a TOTP claim if challenge consumption fails', function (string $change, bool $existing): void {
    $pdo = makeChallengePdo();
    $pdo->exec('CREATE TABLE admin_totp_replay_state (admin_user_id INTEGER PRIMARY KEY, last_counter INTEGER NOT NULL, updated_at TEXT NOT NULL)');
    if ($existing) {
        $pdo->exec("INSERT INTO admin_totp_replay_state VALUES (1, 99, '2026-07-25 08:00:00')");
    }
    $event = $existing ? 'UPDATE' : 'INSERT';
    $pdo->exec('CREATE TRIGGER challenge_race AFTER ' . $event . ' ON admin_totp_replay_state BEGIN UPDATE admin_two_factor_challenges SET ' . $change . '; END');
    $replay = new AdminTotpReplayRepository($pdo);
    $service = new TwoFactorChallengeService(new TwoFactorChallengeRepository($pdo), fn () => true, fn () => false, fn (int $id) => $replay->claimIfNewer($id, 100, '2026-07-25 08:00:00'), 300, fixedNow());
    $token = $service->issue(1);

    expect($service->verifyTotp($token, '123456', 'SECRET')->reason)->toBe('invalid_or_expired')
        ->and($pdo->query('SELECT last_counter FROM admin_totp_replay_state WHERE admin_user_id = 1')->fetchColumn())->toBe($existing ? 99 : false);
})->with([
    'expiry on initial claim' => ["expires_at = '2026-07-25 08:00:00'", false],
    'consumption on later claim' => ["consumed_at = '2026-07-25 08:00:00'", true],
]);

it('keeps TOTP claims and challenge consumption inside the caller transaction', function (): void {
    $pdo = makeChallengePdo();
    $pdo->exec('CREATE TABLE admin_totp_replay_state (admin_user_id INTEGER PRIMARY KEY, last_counter INTEGER NOT NULL, updated_at TEXT NOT NULL)');
    $repo = new TwoFactorChallengeRepository($pdo);
    $replay = new AdminTotpReplayRepository($pdo);
    $service = new TwoFactorChallengeService($repo, fn () => true, fn () => false, fn (int $id) => $replay->claimIfNewer($id, 100, '2026-07-25 08:00:00'), 300, fixedNow());
    $token = $service->issue(1);

    $pdo->beginTransaction();
    expect($service->verifyTotp($token, '123456', 'SECRET')->passed)->toBeTrue()
        ->and($pdo->inTransaction())->toBeTrue();
    $pdo->rollBack();

    expect($pdo->query('SELECT COUNT(*) FROM admin_totp_replay_state')->fetchColumn())->toBe(0)
        ->and($repo->findByTokenHash(hash('sha256', $token))->consumedAt)->toBeNull();
});

it('rejects a claimed TOTP counter on a new challenge', function (): void {
    $pdo = makeChallengePdo();
    $pdo->exec('CREATE TABLE admin_totp_replay_state (admin_user_id INTEGER PRIMARY KEY, last_counter INTEGER NOT NULL, updated_at TEXT NOT NULL)');
    $repo = new TwoFactorChallengeRepository($pdo);
    $replay = new AdminTotpReplayRepository($pdo);
    $service = new TwoFactorChallengeService($repo, fn () => true, fn () => false, fn (int $id) => $replay->claimIfNewer($id, 100, '2026-07-25 08:00:00'), 300, fixedNow());

    expect($service->verifyTotp($service->issue(1), '123456', 'SECRET')->passed)->toBeTrue();
    $token = $service->issue(1);
    expect($service->verifyTotp($token, '123456', 'SECRET')->reason)->toBe('wrong_code')
        ->and($repo->findByTokenHash(hash('sha256', $token))->consumedAt)->toBeNull();
});

it('rolls back a TOTP claim when challenge consumption throws', function (): void {
    $pdo = makeChallengePdo();
    $pdo->exec('CREATE TABLE admin_totp_replay_state (admin_user_id INTEGER PRIMARY KEY, last_counter INTEGER NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec("CREATE TRIGGER fail_consumption BEFORE UPDATE OF consumed_at ON admin_two_factor_challenges BEGIN SELECT RAISE(ABORT, 'consumption failed'); END");
    $replay = new AdminTotpReplayRepository($pdo);
    $service = new TwoFactorChallengeService(new TwoFactorChallengeRepository($pdo), fn () => true, fn () => false, fn (int $id) => $replay->claimIfNewer($id, 100, '2026-07-25 08:00:00'), 300, fixedNow());
    $token = $service->issue(1);

    expect(fn () => $service->verifyTotp($token, '123456', 'SECRET'))->toThrow(PDOException::class, 'consumption failed');
    expect($pdo->inTransaction())->toBeFalse()
        ->and($pdo->query('SELECT COUNT(*) FROM admin_totp_replay_state')->fetchColumn())->toBe(0);
});

it('rejects an expired challenge', function (): void {
    $pdo = makeChallengePdo();
    $repo = new TwoFactorChallengeRepository($pdo);

    // Issue at 08:00 with a 300s TTL...
    $issuer = new TwoFactorChallengeService($repo, fn () => true, fn () => false, fn () => true, 300, fixedNow('2026-07-25 08:00:00'));
    $token = $issuer->issue(9);

    // ...verify at 08:10 (past expiry).
    $verifier = new TwoFactorChallengeService($repo, fn () => true, fn () => false, fn () => true, 300, fixedNow('2026-07-25 08:10:00'));
    $result = $verifier->verifyTotp($token, 'anything', 'SECRET');

    expect($result->passed)->toBeFalse()
        ->and($result->reason)->toBe('invalid_or_expired');
});

it('does not consume a challenge at or after its expiry', function (): void {
    $pdo = makeChallengePdo();
    $repo = new TwoFactorChallengeRepository($pdo);
    $id = $repo->insert(9, hash('sha256', 'token'), '2026-07-25 08:05:00', '2026-07-25 08:00:00');

    expect($repo->markConsumed($id, '2026-07-25 08:05:00'))->toBeFalse()
        ->and($repo->markConsumed($id, '2026-07-25 08:06:00'))->toBeFalse()
        ->and($repo->findByTokenHash(hash('sha256', 'token'))->consumedAt)->toBeNull();
});

it('rejects an unknown token', function (): void {
    $pdo = makeChallengePdo();
    $service = new TwoFactorChallengeService(
        new TwoFactorChallengeRepository($pdo),
        fn () => true,
        fn () => false,
        fn (int $uid, string $secret, string $code): bool => true,
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
        fn (int $uid, string $secret, string $code): bool => true,
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

it('does not spend a recovery code when the challenge becomes unavailable during redemption', function (string $change): void {
    $pdo = makeChallengePdo();
    $pdo->exec('CREATE TABLE admin_user_recovery_codes (id INTEGER PRIMARY KEY, admin_user_id INTEGER NOT NULL, code_hash TEXT NOT NULL, used_at TEXT, created_at TEXT NOT NULL)');
    $insert = $pdo->prepare('INSERT INTO admin_user_recovery_codes (admin_user_id, code_hash, created_at) VALUES (?, ?, ?)');
    $insert->execute([5, password_hash('RECOVERY', PASSWORD_DEFAULT), '2026-07-25 08:00:00']);
    $pdo->exec('CREATE TRIGGER challenge_race AFTER UPDATE OF used_at ON admin_user_recovery_codes BEGIN UPDATE admin_two_factor_challenges SET ' . $change . '; END');
    $repo = new TwoFactorChallengeRepository($pdo);
    $recovery = new AdminRecoveryCodeRepository($pdo);
    $service = new TwoFactorChallengeService($repo, fn () => false, $recovery->redeem(...), fn () => true, 300, fixedNow());
    $token = $service->issue(5);

    $result = $service->verifyRecoveryCode($token, 'RECOVERY');

    expect($result->reason)->toBe('invalid_or_expired')
        ->and($pdo->query('SELECT used_at FROM admin_user_recovery_codes')->fetchColumn())->toBeNull();
})->with([
    'expired at consumption' => ["expires_at = '2026-07-25 08:00:00'"],
    'consumed by another request' => ["consumed_at = '2026-07-25 08:00:00'"],
]);

it('keeps recovery redemption inside the caller transaction', function (): void {
    $pdo = makeChallengePdo();
    $pdo->exec('CREATE TABLE admin_user_recovery_codes (id INTEGER PRIMARY KEY, admin_user_id INTEGER NOT NULL, code_hash TEXT NOT NULL, used_at TEXT, created_at TEXT NOT NULL)');
    $insert = $pdo->prepare('INSERT INTO admin_user_recovery_codes (admin_user_id, code_hash, created_at) VALUES (?, ?, ?)');
    $insert->execute([5, password_hash('RECOVERY', PASSWORD_DEFAULT), '2026-07-25 08:00:00']);
    $repo = new TwoFactorChallengeRepository($pdo);
    $recovery = new AdminRecoveryCodeRepository($pdo);
    $service = new TwoFactorChallengeService($repo, fn () => false, $recovery->redeem(...), fn () => true, 300, fixedNow());
    $token = $service->issue(5);

    $pdo->beginTransaction();
    expect($service->verifyRecoveryCode($token, 'WRONG')->reason)->toBe('wrong_code')
        ->and($pdo->inTransaction())->toBeTrue()
        ->and($service->verifyRecoveryCode($token, 'RECOVERY')->passed)->toBeTrue()
        ->and($pdo->inTransaction())->toBeTrue();
    $pdo->rollBack();

    expect($pdo->query('SELECT used_at FROM admin_user_recovery_codes')->fetchColumn())->toBeNull()
        ->and($repo->findByTokenHash(hash('sha256', $token))->consumedAt)->toBeNull();
});

it('rolls back redemption when the callback throws', function (): void {
    $pdo = makeChallengePdo();
    $pdo->exec('CREATE TABLE admin_user_recovery_codes (id INTEGER PRIMARY KEY, admin_user_id INTEGER NOT NULL, code_hash TEXT NOT NULL, used_at TEXT, created_at TEXT NOT NULL)');
    $insert = $pdo->prepare('INSERT INTO admin_user_recovery_codes (admin_user_id, code_hash, created_at) VALUES (?, ?, ?)');
    $insert->execute([5, password_hash('RECOVERY', PASSWORD_DEFAULT), '2026-07-25 08:00:00']);
    $recovery = new AdminRecoveryCodeRepository($pdo);
    $service = new TwoFactorChallengeService(new TwoFactorChallengeRepository($pdo), fn () => false, function (int $id, string $code) use ($recovery): bool {
        $recovery->redeem($id, $code);
        throw new RuntimeException('failure');
    }, fn () => true, 300, fixedNow());
    $token = $service->issue(5);

    expect(fn () => $service->verifyRecoveryCode($token, 'RECOVERY'))->toThrow(RuntimeException::class, 'failure');
    expect($pdo->inTransaction())->toBeFalse()
        ->and($pdo->query('SELECT used_at FROM admin_user_recovery_codes')->fetchColumn())->toBeNull();
});

it('issuing again clears prior pending challenges for the user', function (): void {
    $pdo = makeChallengePdo();
    $repo = new TwoFactorChallengeRepository($pdo);
    $service = new TwoFactorChallengeService($repo, fn () => true, fn () => false, fn () => true, 300, fixedNow());

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










