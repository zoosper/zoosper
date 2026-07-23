<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * JSONL report sink for report-only rate-limit diagnostics.
 */
final class FileRateLimitReportSink implements RateLimitReportSinkInterface
{
    public function __construct(private string $path)
    {
    }

    public function record(RateLimitReportEvent $event): void
    {
        $directory = dirname($this->path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Unable to create rate limit report directory: ' . $directory);
        }

        $payload = [
            'key' => $event->key,
            'identity_hash' => $event->identityHash,
            'allowed' => $event->allowed,
            'attempts' => $event->attempts,
            'max_attempts' => $event->maxAttempts,
            'retry_after_seconds' => $event->retryAfterSeconds,
            'now' => $event->now,
        ];

        file_put_contents($this->path, json_encode($payload, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
