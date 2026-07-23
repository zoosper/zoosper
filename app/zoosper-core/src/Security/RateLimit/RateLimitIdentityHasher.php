<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Converts caller identity parts into an opaque stable hash.
 */
final class RateLimitIdentityHasher
{
    /** @param list<string> $parts */
    public function hash(array $parts, string $salt = ''): string
    {
        $normalised = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $normalised[] = $part;
            }
        }

        if ($normalised === []) {
            throw new \InvalidArgumentException('At least one non-empty rate limit identity part is required.');
        }

        return hash('sha256', $salt . '|' . implode('|', $normalised));
    }
}
