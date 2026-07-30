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
 *
 * SECURITY/AVAILABILITY FIX (confirmed 2026-07-30, real production incident
 * traced via exception.log + nginx access log): reveal() previously accepted
 * only a SINGLE key. If TWO_FACTOR_ENCRYPTION_KEY was ever changed for ANY
 * reason after an admin had already enrolled in 2FA (including changing it
 * to close the earlier "insecure default key" security gap — see
 * config/two_factor.php's own history), every already-enrolled admin's
 * secret became PERMANENTLY undecryptable with the new key, throwing
 * "Unable to reveal protected 2FA secret." on every single login attempt —
 * a full, permanent lockout with no self-recovery path, confirmed live in
 * production logs (RuntimeException at SecretProtector.php:52, reached via
 * the real /admin/2fa/challenge request path).
 *
 * Fixed by supporting KEY ROTATION, the same pattern Laravel uses via
 * APP_PREVIOUS_KEYS: protect() always encrypts with the CURRENT key only;
 * reveal() tries the current key first, then falls back to trying each
 * PREVIOUS key in order, only throwing if none of them work. This means
 * changing the encryption key going forward is a safe, supported operation
 * — not a lockout risk — as long as the previous key is temporarily kept
 * available via TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS during the rotation
 * window (see config/two_factor.php).
 */
final readonly class SecretProtector
{
    /** @param list<string> $previousKeyMaterials */
    public function __construct(
        private string $keyMaterial,
        private array $previousKeyMaterials = [],
    ) {
    }

    public function protect(string $secret): string
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            throw new RuntimeException('Sodium extension is required to protect 2FA secrets.');
        }

        // Always encrypt with the CURRENT key only — never a previous one.
        $key = sodium_crypto_generichash($this->keyMaterial, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($secret, $nonce, $key);

        if ($ciphertext === false) {
            throw new RuntimeException('Unable to protect two-factor secret.');
        }

        return base64_encode($nonce . $ciphertext);
    }

    /**
     * Reveal a protected secret, trying the current key first and then each
     * previous key in order (key rotation support — see class docblock).
     */
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

        foreach ($this->allKeyMaterials() as $keyMaterial) {
            $key = sodium_crypto_generichash($keyMaterial, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
            $secret = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

            if ($secret !== false) {
                return $secret;
            }
        }

        throw new RuntimeException(
            'Unable to reveal protected two-factor secret. This can happen if '
            . 'TWO_FACTOR_ENCRYPTION_KEY was changed without keeping the old key '
            . 'available in TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS during the rotation '
            . 'window. If this admin genuinely cannot be recovered, their 2FA must '
            . 'be reset via another admin\'s "Reset 2FA" action.'
        );
    }

    /**
     * Whether the given payload can currently be decrypted with ONLY the
     * current key (i.e. it does NOT need any previous key). Used by
     * higher-level code to opportunistically re-protect (re-encrypt with
     * the current key) a secret that was only revealed via a previous key,
     * so the rotation window can eventually be closed.
     */
    public function needsReprotection(string $payload): bool
    {
        if (!function_exists('sodium_crypto_secretbox_open')) {
            return false;
        }

        $decoded = base64_decode($payload, true);
        if ($decoded === false || strlen($decoded) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return false;
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $currentKey = sodium_crypto_generichash($this->keyMaterial, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

        return sodium_crypto_secretbox_open($ciphertext, $nonce, $currentKey) === false;
    }

    /** @return list<string> */
    private function allKeyMaterials(): array
    {
        return [$this->keyMaterial, ...$this->previousKeyMaterials];
    }
}
