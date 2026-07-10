<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Value;

/**
 * One-time TOTP setup payload for admin 2FA enrolment.
 *
 * The secret and provisioning URI are sensitive. They should only be displayed
 * during enrolment and must never be logged, stored in plaintext, or included
 * in audit metadata.
 */
final readonly class TotpSetup
{
    public function __construct(
        public string $secret,
        public string $provisioningUri,
    ) {
    }
}
