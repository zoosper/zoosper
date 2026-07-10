<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Totp;

/**
 * Verifies time-based one-time passwords.
 *
 * OTP values are never logged or stored. Verification compares user input using
 * hash_equals and allows a small configurable step window for clock drift.
 */
final readonly class TotpVerifier
{
    public function __construct(private int $period = 30, private int $digits = 6, private int $window = 1)
    {
    }

    public function verify(string $secret, string $otp, ?int $timestamp = null): bool
    {
        $otp = preg_replace('/\D+/', '', $otp) ?? '';
        if (strlen($otp) !== $this->digits) {
            return false;
        }

        $time = $timestamp ?? time();
        $counter = intdiv($time, $this->period);

        for ($offset = -$this->window; $offset <= $this->window; $offset++) {
            if (hash_equals($this->code($secret, $counter + $offset), $otp)) {
                return true;
            }
        }

        return false;
    }

    private function code(string $secret, int $counter): string
    {
        $binarySecret = Base32::decode($secret);
        $time = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $time, $binarySecret, true);
        $offset = ord($hash[19]) & 0xf;
        $value = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad((string) ($value % (10 ** $this->digits)), $this->digits, '0', STR_PAD_LEFT);
    }
}
