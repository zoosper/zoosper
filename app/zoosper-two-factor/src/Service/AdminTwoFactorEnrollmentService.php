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

    public function revealSecret(int $adminUserId): ?string
    {
        $protected = $this->repository->findProtectedSecret($adminUserId);
        if ($protected === null) {
            return null;
        }

        return $this->protector->reveal($protected);
    }
}
