<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

/**
 * Verifies TOTP codes for admin 2FA challenges.
 *
 * OTP values must never be logged. This verifier accepts only numeric codes and
 * compares generated values using hash_equals to avoid timing-sensitive string
 * comparisons.
 */
final readonly class TotpVerifier
{
    public function __construct(
        private Base32 $base32 = new Base32(),
        private int $period = 30,
        private int $digits = 6,
        private int $window = 1,
    ) {
    }

    /**
     * Verify a TOTP code against a Base32 secret.
     */
    public function verify(string $secret, string $code, ?int $timestamp = null): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';
        if ($code === '' || !ctype_digit($code)) {
            return false;
        }

        $timestamp ??= time();
        $counter = intdiv($timestamp, $this->period);

        for ($offset = -$this->window; $offset <= $this->window; $offset++) {
            if (hash_equals($this->generate($secret, $counter + $offset), str_pad($code, $this->digits, '0', STR_PAD_LEFT))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate one TOTP code for a counter value.
     */
    private function generate(string $secret, int $counter): string
    {
        $key = $this->base32->decode($secret);
        $binaryCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
        $truncated = unpack('N', substr($hash, $offset, 4))[1] & 0x7fffffff;
        $code = $truncated % (10 ** $this->digits);

        return str_pad((string) $code, $this->digits, '0', STR_PAD_LEFT);
    }
}
