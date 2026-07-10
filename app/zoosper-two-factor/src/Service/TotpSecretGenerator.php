<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

/**
 * Generates Base32-compatible TOTP secrets.
 *
 * The returned value is a sensitive authentication secret. It must be protected
 * before storage and must never be written to application logs.
 */
final readonly class TotpSecretGenerator
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generate(int $length = 32): string
    {
        $secret = '';
        $alphabetLength = strlen(self::ALPHABET);

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $secret;
    }
}
