<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

/**
 * Hashes and verifies admin 2FA recovery codes.
 *
 * Recovery codes are authentication secrets. The plain value should only exist
 * transiently during generation or verification and must never be logged.
 */
final readonly class RecoveryCodeHasher
{
    public function hash(string $code): string
    {
        return password_hash($code, PASSWORD_DEFAULT);
    }

    public function verify(string $code, string $hash): bool
    {
        return password_verify($code, $hash);
    }
}
