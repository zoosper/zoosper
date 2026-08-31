<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * JSONL report sink for report-only rate-limit diagnostics.
 */
final class FileRateLimitReportSink implements RateLimitReportSinkInterface
{
    public function __construct(
        private string $path,
        private int $maxSizeBytes = 10_485_760,
        private int $maxFiles = 5,
    ) {
    }

    public function record(RateLimitReportEvent $event): void
    {
        $directory = dirname($this->path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Unable to create rate limit report directory: ' . $directory);
        }

        $this->rotateIfNeeded();

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

    private function rotateIfNeeded(): void
    {
        if (!is_file($this->path) || filesize($this->path) < $this->maxSizeBytes) {
            return;
        }

        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $current = $this->path . '.' . $i;
            $next = $this->path . '.' . ($i + 1);
            if (is_file($current)) {
                if ($i + 1 > $this->maxFiles) {
                    @unlink($current);
                } else {
                    @rename($current, $next);
                }
            }
        }

        @rename($this->path, $this->path . '.1');
    }
}










