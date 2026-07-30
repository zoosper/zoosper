<?php

declare(strict_types=1);

use Zoosper\TwoFactor\Crypto\SecretProtector;

/**
 * SECURITY/AVAILABILITY REGRESSION TEST — directly reproduces the real
 * production incident (confirmed via exception.log + nginx access log,
 * 2026-07-30): an admin's 2FA secret, encrypted under an OLD
 * TWO_FACTOR_ENCRYPTION_KEY, became permanently undecryptable the moment
 * the key was rotated to a new value — a full login lockout with no
 * self-recovery path.
 *
 * File placement: app/zoosper-two-factor/tests/Unit/Crypto/SecretProtectorKeyRotationTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
it('REPRODUCES THE REAL INCIDENT: reveal() throws when the key changed and no previous key is retained (old, unfixed behaviour)', function (): void {
    $secret = 'JBSWY3DPEHPK3PXP';
    $oldKey = 'the-original-key-this-admin-enrolled-under';
    $newKey = 'a-brand-new-rotated-key';

    // Encrypt under the OLD key (simulating an admin who enrolled before
    // the key was ever rotated).
    $oldProtector = new SecretProtector($oldKey);
    $protectedUnderOldKey = $oldProtector->protect($secret);

    // Now simulate the key rotation with NO previous-key support at all
    // (the exact, unfixed pre-incident configuration) — reveal() must
    // throw, reproducing the real production error.
    $newProtectorWithNoPreviousKeys = new SecretProtector($newKey);

    expect(fn () => $newProtectorWithNoPreviousKeys->reveal($protectedUnderOldKey))
        ->toThrow(RuntimeException::class, 'Unable to reveal protected two-factor secret');
});

it('THE FIX: reveal() succeeds when the old key is supplied as a previous key after rotation', function (): void {
    $secret = 'JBSWY3DPEHPK3PXP';
    $oldKey = 'the-original-key-this-admin-enrolled-under';
    $newKey = 'a-brand-new-rotated-key';

    $oldProtector = new SecretProtector($oldKey);
    $protectedUnderOldKey = $oldProtector->protect($secret);

    // The fix: the new protector is given the old key as a "previous key".
    $newProtectorWithPreviousKey = new SecretProtector($newKey, [$oldKey]);

    $revealed = $newProtectorWithPreviousKey->reveal($protectedUnderOldKey);

    expect($revealed)->toBe($secret);
});

it('tries multiple previous keys in order until one works', function (): void {
    $secret = 'JBSWY3DPEHPK3PXP';
    $veryOldKey = 'oldest-key';
    $lessOldKey = 'middle-key';
    $currentKey = 'current-key';

    $protectedUnderVeryOldKey = (new SecretProtector($veryOldKey))->protect($secret);

    // Both previous keys supplied, in most-recent-first order — only the
    // very old key will actually work, proving the fallback loop tries
    // every candidate, not just the first one.
    $protector = new SecretProtector($currentKey, [$lessOldKey, $veryOldKey]);

    expect($protector->reveal($protectedUnderVeryOldKey))->toBe($secret);
});

it('always encrypts with the CURRENT key only, never a previous one', function (): void {
    $secret = 'JBSWY3DPEHPK3PXP';
    $currentKey = 'current-key';
    $previousKey = 'previous-key';

    $protector = new SecretProtector($currentKey, [$previousKey]);
    $protected = $protector->protect($secret);

    // A protector with ONLY the previous key (no current key access) must
    // NOT be able to decrypt something protect() just created — proving
    // protect() never used the previous key.
    $previousOnlyProtector = new SecretProtector($previousKey);

    expect(fn () => $previousOnlyProtector->reveal($protected))
        ->toThrow(RuntimeException::class);

    // But the real current-key protector can decrypt its own output, as normal.
    expect($protector->reveal($protected))->toBe($secret);
});

it('needsReprotection() correctly identifies a secret that was only decryptable via a previous key', function (): void {
    $secret = 'JBSWY3DPEHPK3PXP';
    $oldKey = 'old-key';
    $newKey = 'new-key';

    $protectedUnderOldKey = (new SecretProtector($oldKey))->protect($secret);
    $rotatedProtector = new SecretProtector($newKey, [$oldKey]);

    expect($rotatedProtector->needsReprotection($protectedUnderOldKey))->toBeTrue();

    // Confirms the opposite case too: something already encrypted with the
    // CURRENT key does NOT need re-protection.
    $protectedUnderNewKey = $rotatedProtector->protect($secret);
    expect($rotatedProtector->needsReprotection($protectedUnderNewKey))->toBeFalse();
});

it('still works correctly with no key rotation at all (no regression for the common case)', function (): void {
    $secret = 'JBSWY3DPEHPK3PXP';
    $protector = new SecretProtector('a-single-key-no-rotation');

    $protected = $protector->protect($secret);

    expect($protector->reveal($protected))->toBe($secret);
    expect($protector->needsReprotection($protected))->toBeFalse();
});
