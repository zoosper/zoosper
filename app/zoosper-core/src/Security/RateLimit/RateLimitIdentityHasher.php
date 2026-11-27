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

        $components = [$salt, ...$normalised];
        foreach ($components as $component) {
            if (str_contains($component, '|') || str_contains($component, "\0")) {
                $encoded = "\0zoosper-rate-limit-v2:";
                foreach ($components as $value) {
                    $encoded .= strlen($value) . ':' . $value;
                }

                return hash('sha256', $encoded);
            }
        }

        return hash('sha256', $salt . '|' . implode('|', $normalised));
    }
}










