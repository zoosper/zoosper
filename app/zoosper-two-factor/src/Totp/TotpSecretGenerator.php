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
        if ($bytes < 1) {
            throw new \ValueError('random_bytes(): Argument #1 ($length) must be greater than 0');
        }

        return Base32::encode(random_bytes($bytes));
    }
}










