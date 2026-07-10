<?php

declare(strict_types=1);

namespace Zoosper\Core\Log;

use Throwable;

final readonly class ErrorHandler
{
    public function __construct(private LocalLogger $logger)
    {
    }

    public function register(): void
    {
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            $this->logger->error($message, [
                'severity' => $severity,
                'file' => $file,
                'line' => $line,
            ]);

            return false;
        });

        register_shutdown_function(function (): void {
            $error = error_get_last();
            if ($error === null) {
                return;
            }

            if (!in_array((int) ($error['type'] ?? 0), [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }

            $this->logger->critical((string) ($error['message'] ?? 'Fatal error'), [
                'type' => $error['type'] ?? null,
                'file' => $error['file'] ?? null,
                'line' => $error['line'] ?? null,
            ]);
        });
    }

    /** @param array<string, mixed> $context */
    public function logException(Throwable $exception, array $context = []): void
    {
        $this->logger->exception($exception, $context);
    }
}
