<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Recovery;

/**
 * Generates one-time recovery codes and hashes them for storage.
 *
 * Plain recovery codes are sensitive and must only be shown once during setup.
 */
final readonly class RecoveryCodeGenerator
{
    /** @return list<string> */
    public function generate(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(8)), 0, 8) . '-' . substr(bin2hex(random_bytes(8)), 0, 8));
        }
        return $codes;
    }

    public function hash(string $code): string
    {
        return password_hash($code, PASSWORD_DEFAULT);
    }
}
