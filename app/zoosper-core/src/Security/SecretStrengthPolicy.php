<?php

declare(strict_types=1);

namespace Zoosper\Core\Security;

/** Canonical non-disclosing strength contract for generated application secrets. */
final class SecretStrengthPolicy
{
    private const MINIMUM_BYTES = 32;

    /** @var list<string> */
    private const EXACT_PLACEHOLDERS = [
        'change-me', 'change-me-before-production', 'secret', 'changeme',
        'placeholder', 'default', 'password', 'test', 'null',
    ];

    public static function isStrong(string $value): bool
    {
        $trimmed = trim($value);
        if ($trimmed === '' || self::isPlaceholder($trimmed)) {
            return false;
        }

        if (str_starts_with(strtolower($trimmed), 'base64:')) {
            $decoded = base64_decode(substr($trimmed, 7), true);
            return $decoded !== false && strlen($decoded) >= self::MINIMUM_BYTES;
        }

        return strlen($trimmed) >= self::MINIMUM_BYTES;
    }

    public static function isPlaceholder(string $value): bool
    {
        $lower = strtolower(trim($value));
        if (str_starts_with($lower, 'base64:')) {
            $decoded = base64_decode(substr($value, 7), true);
            if ($decoded !== false) {
                $lower = strtolower(trim($decoded));
            }
        }

        return in_array($lower, self::EXACT_PLACEHOLDERS, true)
            || str_contains($lower, 'change-me')
            || str_contains($lower, 'placeholder');
    }
}
