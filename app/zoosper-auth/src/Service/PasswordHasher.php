<?php

declare(strict_types=1);

namespace Zoosper\Auth\Service;

final readonly class PasswordHasher
{
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * True when the given hash was produced with a different algorithm or a
     * weaker cost/parameters than PHP's current PASSWORD_DEFAULT — i.e. it
     * should be regenerated. Additive method; hash()/verify() unchanged.
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
