<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

use Zoosper\TwoFactor\Repository\AdminRecoveryCodeRepository;
use Zoosper\TwoFactor\Repository\AdminTwoFactorRepository;
use Zoosper\TwoFactor\Value\TotpSetup;

/**
 * Coordinates admin TOTP enrolment without logging sensitive secrets.
 *
 * This service creates setup payloads, verifies an enrolment OTP, stores the
 * protected TOTP secret and stores hashed recovery codes. Callers must display
 * the generated recovery codes once and must not log them.
 */
final readonly class AdminTwoFactorEnrolmentService
{
    public function __construct(
        private TotpSecretGenerator $secrets,
        private TotpProvisioningUriGenerator $uris,
        private TotpVerifier $verifier,
        private TwoFactorSecretProtector $protector,
        private RecoveryCodeGenerator $recoveryCodes,
        private RecoveryCodeHasher $recoveryCodeHasher,
        private AdminTwoFactorRepository $profiles,
        private AdminRecoveryCodeRepository $recoveryCodeRepository,
    ) {
    }

    /**
     * Create a one-time TOTP setup payload for display during enrolment.
     */
    public function createSetup(string $adminEmail): TotpSetup
    {
        $secret = $this->secrets->generate();

        return new TotpSetup(
            secret: $secret,
            provisioningUri: $this->uris->generate($adminEmail, $secret),
        );
    }

    /**
     * Verify setup OTP, persist protected secret and rotate recovery codes.
     *
     * @return list<string> Plain recovery codes to show once to the admin user.
     */
    public function confirmSetup(int $adminUserId, string $secret, string $otp): array
    {
        if (!$this->verifier->verify($secret, $otp)) {
            return [];
        }

        $this->profiles->saveTotpProfile($adminUserId, $this->protector->protect($secret));

        $codes = $this->recoveryCodes->generate();
        $hashes = array_map(fn (string $code): string => $this->recoveryCodeHasher->hash($code), $codes);
        $this->recoveryCodeRepository->replaceForAdminUser($adminUserId, $hashes);

        return $codes;
    }
}
