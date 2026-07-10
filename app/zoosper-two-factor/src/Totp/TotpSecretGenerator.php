<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Totp;

/**
 * Generates random TOTP setup secrets.
 *
 * Generated secrets are sensitive. They should only be shown during enrolment
 * and must never be written to logs, audit metadata or email logs.
 */
final readonly class TotpSecretGenerator
{
    public function generate(int $bytes = 20): string
    {
        return Base32::encode(random_bytes($bytes));
    }
}
