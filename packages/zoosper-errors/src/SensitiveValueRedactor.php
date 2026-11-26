<?php

declare(strict_types=1);

namespace Zoosper\Errors;

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
     * @param array<array-key, mixed> $data
     * @return array<array-key, mixed>
     */
    public function redactArray(array $data): array
    {
        $redacted = [];
        foreach ($data as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $redacted[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $redacted[$key] = $this->redactArray($value);
                continue;
            }

            $redacted[$key] = $value;
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











