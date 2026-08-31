<?php

declare(strict_types=1);

/**
 * DEAD CODE CLEANUP REGRESSION TEST — confirms a second, unused 2FA
 * crypto/enrolment stack (found via a targeted grep, confirmed to have NO
 * external callers beyond the dead chain itself) has been removed, and
 * that the LIVE stack — which these dead classes were never actually
 * wired to replace — remains completely intact.
 *
 * Background: two independent implementations of 2FA secret-protection
 * and repository logic existed side by side in this codebase:
 * - LIVE (wired via app/zoosper-two-factor/config/services.php, confirmed
 *   directly earlier this session): SecretProtector (Crypto\, libsodium),
 *   TotpVerifier (Totp\), TotpSecretGenerator (Totp\), RecoveryCodeGenerator
 *   (Recovery\), AdminTwoFactorEnrollmentService (Service\, American
 *   spelling), AdminTwoFactorEnrollmentRepository (Repository\, American
 *   spelling, note the double-l).
 * - DEAD (confirmed via grep — referenced ONLY by each other, never by
 *   config/services.php or any controller): TwoFactorSecretProtector
 *   (Service\, OpenSSL AES-256-GCM), AdminTwoFactorRepository (Repository\,
 *   no "Enrollment" in the name), AdminTwoFactorEnrolmentService (Service\,
 *   British spelling, single-l).
 *
 * File placement: app/zoosper-two-factor/tests/Unit/DeadCodeCleanupTest.php.
 * This test does not need to resolve a repo-root path (unlike some other
 * tests this session), since it only checks class_exists() against
 * autoloaded namespaces — no dirname()-based path arithmetic involved.
 */
it('confirms the dead 2FA enrolment/crypto stack has been removed', function (): void {
    expect(class_exists('Zoosper\TwoFactor\Service\AdminTwoFactorEnrolmentService'))->toBeFalse();
    expect(class_exists('Zoosper\TwoFactor\Service\TwoFactorSecretProtector'))->toBeFalse();
    expect(class_exists('Zoosper\TwoFactor\Repository\AdminTwoFactorRepository'))->toBeFalse();
});

it('confirms the live 2FA stack these dead classes were never wired to replace remains intact', function (): void {
    expect(class_exists(\Zoosper\TwoFactor\Crypto\SecretProtector::class))->toBeTrue();
    expect(class_exists(\Zoosper\TwoFactor\Totp\TotpVerifier::class))->toBeTrue();
    expect(class_exists(\Zoosper\TwoFactor\Totp\TotpSecretGenerator::class))->toBeTrue();
    expect(class_exists(\Zoosper\TwoFactor\Recovery\RecoveryCodeGenerator::class))->toBeTrue();
    expect(class_exists(\Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService::class))->toBeTrue();
    expect(class_exists(\Zoosper\TwoFactor\Repository\AdminTwoFactorEnrollmentRepository::class))->toBeTrue();
});










