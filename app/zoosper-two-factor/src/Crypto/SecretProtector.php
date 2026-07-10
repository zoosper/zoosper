<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Crypto;

use RuntimeException;

/**
 * Protects TOTP secrets before storage.
 *
 * The protector uses Sodium secretbox and stores only ciphertext. Raw secrets
 * must never be logged, emailed, stored in audit metadata, or shown after the
 * enrolment confirmation step.
 */
final readonly class SecretProtector
{
    public function __construct(private string $keyMaterial)
    {
    }

    public function protect(string $secret): string
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            throw new RuntimeException('Sodium extension is required to protect 2FA secrets.');
        }

        $key = sodium_crypto_generichash($this->keyMaterial, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($secret, $nonce, $key);

        return base64_encode($nonce . $ciphertext);
    }

    public function reveal(string $payload): string
    {
        if (!function_exists('sodium_crypto_secretbox_open')) {
            throw new RuntimeException('Sodium extension is required to reveal 2FA secrets.');
        }

        $decoded = base64_decode($payload, true);
        if ($decoded === false || strlen($decoded) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new RuntimeException('Invalid protected secret payload.');
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key = sodium_crypto_generichash($this->keyMaterial, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        $secret = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if ($secret === false) {
            throw new RuntimeException('Unable to reveal protected 2FA secret.');
        }

        return $secret;
    }
}
