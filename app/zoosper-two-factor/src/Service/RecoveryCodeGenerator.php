<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

/**
 * Generates one-time recovery codes for admin 2FA enrolment.
 *
 * Generated codes are intended to be shown once to the admin user. Only hashes
 * should be stored, and codes must never be written to logs.
 */
final readonly class RecoveryCodeGenerator
{
    /**
     * @return list<string>
     */
    public function generate(int $count = 10, int $bytes = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes($bytes)));
        }

        return $codes;
    }
}
