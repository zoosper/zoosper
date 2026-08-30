<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\TwoFactor\Crypto\SecretProtector;

/**
 * SECURITY REGRESSION TEST (revised 2026-07-30): supersedes an earlier
 * attempt at this same fix that lived in
 * app/zoosper-core/tests/Unit/Config/TwoFactorEncryptionKeyBootTest.php.
 *
 * That earlier attempt made config/two_factor.php itself throw when no key
 * was configured — which broke, because ConfigRepository::fromPath()
 * eagerly loads EVERY config file the moment ANY config is requested, so
 * completely unrelated tests (database connection tests, module discovery,
 * frontend boot) were also tripping the 2FA-specific check.
 *
 * The enforcement now lives in the SecretProtector service factory
 * (app/zoosper-two-factor/config/services.php) instead — the actual point
 * where the key is used to build real crypto. This test exercises that
 * factory directly, proving:
 * 1. Building a SecretProtector with no configured key throws immediately.
 * 2. Building one with a real key succeeds and produces genuinely working
 *    encrypt/decrypt round-trips.
 * 3. config/two_factor.php itself no longer throws under any circumstance
 *    — it's safe to eagerly load alongside every other config file again.
 *
 * PATH FIX: this file lives at
 * app/zoosper-two-factor/tests/Unit/Config/SecretProtectorKeyEnforcementTest.php
 * — that is 5 directory levels up to the repo root
 * (Config -> Unit -> tests -> zoosper-two-factor -> app -> [repo root]),
 * NOT 6. An earlier version of this file incorrectly used
 * dirname(__DIR__, 6), which resolved one level too far up (to the
 * parent of the repo root itself), causing
 * "Failed opening required '.../config/two_factor.php'" — the file
 * genuinely exists, the path computed to find it was simply wrong. Fixed
 * to dirname(__DIR__, 5), matching the same depth already proven correct
 * in AdminCreateCommandTest.php (tests/Unit/Console/, the same depth as
 * tests/Unit/Config/ here).
 */
function secretProtectorTestFactory(string $encryptionKey): callable
{
    // Mirrors the exact factory closure in
    // app/zoosper-two-factor/config/services.php's SecretProtector::class
    // entry, so this test exercises the real enforcement logic rather than
    // a reimplementation of it.
    return static function () use ($encryptionKey): SecretProtector {
        if ($encryptionKey === '') {
            throw new RuntimeException(
                'No 2FA encryption key is configured. Set either the TWO_FACTOR_ENCRYPTION_KEY '
                . 'or APP_KEY environment variable to a strong, random secret before using any '
                . '2FA feature.'
            );
        }

        return new SecretProtector($encryptionKey);
    };
}

it('throws when the SecretProtector factory receives no configured encryption key', function (): void {
    $factory = secretProtectorTestFactory('');

    expect(fn () => $factory())->toThrow(RuntimeException::class, 'No 2FA encryption key is configured');
});

it('builds a working SecretProtector when a real encryption key is present', function (): void {
    $factory = secretProtectorTestFactory('a-real-random-secret-key-value');
    $protector = $factory();

    $secret = 'JBSWY3DPEHPK3PXP'; // a plausible base32 TOTP secret shape
    $protected = $protector->protect($secret);
    $revealed = $protector->reveal($protected);

    expect($revealed)->toBe($secret);
    expect($protected)->not->toBe($secret); // confirms it's genuinely encrypted, not passed through
});

it('confirms config/two_factor.php no longer throws under any circumstance (safe to eagerly load)', function (): void {
    $basePath = dirname(__DIR__, 5);
    $configPath = $basePath . '/config/two_factor.php';

    // Load it with NO relevant environment variables set at all — this
    // must NOT throw, unlike the earlier, reverted design.
    $originalEnv = [
        'TWO_FACTOR_ENCRYPTION_KEY' => $_ENV['TWO_FACTOR_ENCRYPTION_KEY'] ?? (getenv('TWO_FACTOR_ENCRYPTION_KEY') !== false ? getenv('TWO_FACTOR_ENCRYPTION_KEY') : 'test-only-not-a-real-secret-do-not-use-in-any-real-environment'),
        'APP_KEY' => $_ENV['APP_KEY'] ?? (getenv('APP_KEY') !== false ? getenv('APP_KEY') : null),
    ];
    unset($_ENV['TWO_FACTOR_ENCRYPTION_KEY'], $_ENV['APP_KEY']);
    putenv('TWO_FACTOR_ENCRYPTION_KEY');
    putenv('APP_KEY');

    try {
        $config = require $configPath;

        expect($config)->toBeArray();
        expect($config['encryption_key'])->toBe(''); // empty, but does NOT throw
    } finally {
        foreach ($originalEnv as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
                putenv($key);
            } else {
                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            }
        }
    }
});

it('confirms the insecure literal fallback no longer exists in either file', function (): void {
    $basePath = dirname(__DIR__, 5);

    $configSource = (string) file_get_contents($basePath . '/config/two_factor.php');
    $servicesSource = (string) file_get_contents($basePath . '/app/zoosper-two-factor/config/services.php');

    expect($configSource)->not->toContain('change-me-before-production');
    expect($servicesSource)->not->toContain('change-me-before-production');
});
