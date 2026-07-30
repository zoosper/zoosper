<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

use Zoosper\TwoFactor\Crypto\SecretProtector;
use Zoosper\TwoFactor\Recovery\RecoveryCodeGenerator;
use Zoosper\TwoFactor\Repository\AdminTwoFactorEnrollmentRepository;
use Zoosper\TwoFactor\Totp\TotpSecretGenerator;
use Zoosper\TwoFactor\Totp\TotpVerifier;

final readonly class AdminTwoFactorEnrollmentService
{
    public function __construct(
        private AdminTwoFactorEnrollmentRepository $repository,
        private TotpSecretGenerator $secrets,
        private TotpVerifier $verifier,
        private SecretProtector $protector,
        private RecoveryCodeGenerator $recoveryCodes,
        private string $issuer,
        private int $recoveryCodeCount = 8,
    ) {
    }

    public function requiresEnrollment(int $adminUserId): bool
    {
        return !$this->repository->hasActiveEnrollment($adminUserId);
    }

    public function startSetup(string $email): array
    {
        $secret = $this->secrets->generate();
        return [
            'secret' => $secret,
            'uri' => 'otpauth://totp/' . rawurlencode($this->issuer . ':' . $email) . '?secret=' . rawurlencode($secret) . '&issuer=' . rawurlencode($this->issuer),
        ];
    }

    public function confirm(int $adminUserId, string $secret, string $otp): array
    {
        if (!$this->verifier->verify($secret, $otp)) {
            return [];
        }

        $codes = $this->recoveryCodes->generate($this->recoveryCodeCount);
        $hashes = array_map(fn (string $code): string => $this->recoveryCodes->hash($code), $codes);
        $this->repository->saveConfirmedEnrollment($adminUserId, $this->protector->protect($secret), $hashes);

        return $codes;
    }

    /**
     * KEY ROTATION FIX (confirmed 2026-07-30, real production lockout
     * incident): reveal a stored secret. If it can only be decrypted using
     * a PREVIOUS encryption key (i.e. TWO_FACTOR_ENCRYPTION_KEY has been
     * rotated since this admin last logged in), this now OPPORTUNISTICALLY
     * re-encrypts the secret with the CURRENT key and re-saves it —
     * closing the rotation window for this admin automatically, on their
     * next successful login, with no manual intervention needed. This is
     * silent and safe: it only ever re-saves the SAME secret, re-encrypted
     * with a different key; it never changes what the secret actually is.
     *
     * If re-protection fails for any reason, the original (successfully
     * revealed) secret is still returned — a failed opportunistic
     * re-encryption must never turn a successful login into a failure.
     */
    public function revealSecret(int $adminUserId): ?string
    {
        $protected = $this->repository->findProtectedSecret($adminUserId);
        if ($protected === null) {
            return null;
        }

        $secret = $this->protector->reveal($protected);

        if ($this->protector->needsReprotection($protected)) {
            try {
                $reprotected = $this->protector->protect($secret);
                $this->repository->updateProtectedSecret($adminUserId, $reprotected);
            } catch (\Throwable) {
                // Deliberately swallowed: a failed opportunistic
                // re-encryption must not turn an otherwise-successful
                // login into a failure. The rotation window simply stays
                // open for this admin until the next successful login.
            }
        }

        return $secret;
    }
}
