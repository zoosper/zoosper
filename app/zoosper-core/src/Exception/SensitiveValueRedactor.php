<?php

declare(strict_types=1);

namespace Zoosper\Core\Exception;

/**
 * Redacts sensitive values before they are displayed or logged in diagnostics.
 */
final readonly class SensitiveValueRedactor
{
    /** @var list<string> */
    private const SENSITIVE_TOKENS = [
        'password',
        'passwd',
        'secret',
        'token',
        'key',
        'authorization',
        'cookie',
        'session',
        'csrf',
        'otp',
        'totp',
        'recovery',
        'payment',
        'card',
        'cvv',
        'private',
    ];

    /**
     * Redact a diagnostic array recursively.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function redactArray(array $data): array
    {
        $redacted = [];
        foreach ($data as $key => $value) {
            $keyString = (string) $key;
            if ($this->isSensitiveKey($keyString)) {
                $redacted[$keyString] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $redacted[$keyString] = $this->redactArray($value);
                continue;
            }

            $redacted[$keyString] = $value;
        }

        return $redacted;
    }

    public function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);
        foreach (self::SENSITIVE_TOKENS as $token) {
            if (str_contains($key, $token)) {
                return true;
            }
        }

        return false;
    }
}
