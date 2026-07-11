<?php

declare(strict_types=1);

namespace Zoosper\Core\Log;

use Throwable;
use Zoosper\Core\Exception\SensitiveValueRedactor;
use Zoosper\Core\Exception\ZoosperException;

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
        $redactor = new SensitiveValueRedactor();
        $context = $redactor->redactArray($context);

        if ($exception instanceof ZoosperException) {
            $context = array_merge($context, $redactor->redactArray([
                'zoosper_context' => $exception->context(),
                'zoosper_suggestion' => $exception->suggestion(),
                'zoosper_docs_url' => $exception->docsUrl(),
                'zoosper_details' => $exception->details(),
            ]));
        }

        $this->logger->exception($exception, $context);
    }
}
