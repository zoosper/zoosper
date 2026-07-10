<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

use RuntimeException;

/**
 * Protects admin 2FA secrets before database storage.
 *
 * This foundation uses OpenSSL authenticated encryption when available. The key
 * must come from deployment configuration and must not be committed to source
 * control or written to logs.
 */
final readonly class TwoFactorSecretProtector
{
    public function __construct(private string $key)
    {
    }

    public function protect(string $secret): string
    {
        $key = $this->normalisedKey();
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($secret, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false || $tag === '') {
            throw new RuntimeException('Unable to protect two-factor secret.');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    public function reveal(string $payload): string
    {
        $decoded = base64_decode($payload, true);
        if ($decoded === false || strlen($decoded) < 29) {
            throw new RuntimeException('Invalid protected two-factor secret payload.');
        }

        $iv = substr($decoded, 0, 12);
        $tag = substr($decoded, 12, 16);
        $ciphertext = substr($decoded, 28);
        $secret = openssl_decrypt($ciphertext, 'aes-256-gcm', $this->normalisedKey(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($secret === false) {
            throw new RuntimeException('Unable to reveal two-factor secret.');
        }

        return $secret;
    }

    private function normalisedKey(): string
    {
        if ($this->key === '') {
            throw new RuntimeException('Two-factor encryption key is not configured.');
        }

        return hash('sha256', $this->key, true);
    }
}
