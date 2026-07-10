<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Model;

/**
 * Immutable admin 2FA profile record.
 *
 * The secret stored in this model must already be protected ciphertext. Plain
 * TOTP secrets, OTP values and recovery codes must never be logged or persisted
 * without protection.
 */
final readonly class AdminTwoFactorProfile
{
    public function __construct(
        public int $id,
        public int $adminUserId,
        public string $method,
        public string $secretCiphertext,
        public ?string $enabledAt,
        public ?string $lastVerifiedAt,
    ) {
    }
}
