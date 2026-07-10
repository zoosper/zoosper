<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

use RuntimeException;

/**
 * Minimal RFC 4648 Base32 decoder for TOTP secrets.
 *
 * This class only decodes secrets for OTP verification. It must not log input
 * values because TOTP secrets are authentication secrets.
 */
final readonly class Base32
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Decode a Base32 string into raw bytes.
     */
    public function decode(string $value): string
    {
        $value = strtoupper(rtrim(str_replace(' ', '', $value), '='));
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        foreach (str_split($value) as $char) {
            $index = strpos(self::ALPHABET, $char);
            if ($index === false) {
                throw new RuntimeException('Invalid Base32 value.');
            }

            $buffer = ($buffer << 5) | $index;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }

        return $output;
    }
}
