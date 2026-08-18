<?php

declare(strict_types=1);

namespace Zoosper\Core\Error;

use Throwable;
use Zoosper\Logger\Contract\LoggerInterface;
use Zoosper\Errors\ExceptionDisplayer;
use Zoosper\Errors\SensitiveValueRedactor;
use Zoosper\Errors\ZoosperException;
use Zoosper\Core\Http\Response;

/**
 * BUG FOUND AND FIXED (confirmed 2026-07-30, while wiring Marko for real):
 * register() previously called set_error_handler() (for PHP-engine errors)
 * and registered a shutdown function (for fatals) — but NEVER called
 * set_exception_handler() at all. This meant genuinely uncaught exceptions
 * never reached the richer logException() path (redaction, ZoosperException
 * context/suggestion/details extraction) — only the shutdown function's
 * generic CRITICAL fatal-error log entry. Fixed by also registering a real
 * exception handler.
 *
 * ARCHITECTURAL BOUNDARY FIX (2026-07-30, same day): this class previously
 * imported 6 Marko\* classes directly to build the on-screen/CLI display —
 * meaning zoosper-core's own composer.json had to declare marko/errors and
 * marko/errors-simple as direct dependencies, purely because of this one
 * class. That is exactly the kind of "Core knows a feature-specific
 * concept" coupling this codebase has deliberately avoided elsewhere (the
 * same "depend on an interface/boundary owned by the right module, not a
 * concrete feature-module implementation" pattern already proven for the
 * site-lookup and admin-layout-rendering concerns — deliberately not
 * naming those other classes verbatim here, since this file is scanned by
 * an automated architecture test that flags any literal feature-module
 * namespace string appearing anywhere in zoosper-core/src, including
 * inside comments).
 *
 * Fixed by moving all Marko-specific display logic into a new
 * Zoosper\Errors\ExceptionDisplayer class, owned by the zoosper-errors
 * package (which already owns the real, direct dependency on Marko via
 * ZoosperException extending MarkoException). This class now only ever
 * calls (new ExceptionDisplayer())->display($exception) — it has ZERO
 * knowledge that Marko is involved at all. zoosper-core's composer.json no
 * longer needs marko/errors or marko/errors-simple declared directly; it
 * only needs zoosper/errors, which transitively provides them.
 *
 * MARKO INTEGRATION (unchanged in behaviour from the previous version —
 * only the code's LOCATION changed): register() logs every uncaught
 * exception via the existing LocalLogger (completely unchanged), THEN
 * delegates actual on-screen/CLI display to Marko's real formatters via
 * ExceptionDisplayer — genuine use of marko/errors-simple, not a
 * reimplementation of it.
 *
 * DELIBERATELY NOT DONE: replacing this class with Marko's own
 * SimpleErrorHandler wholesale. That class only displays/outputs — it has
 * no file-based, redacted, structured logging via anything resembling
 * LocalLogger. Swapping wholesale would have silently lost all existing
 * log-file output in exchange for a prettier screen — an unacceptable
 * trade. This class instead COMPOSES: your existing logging stays exactly
 * as it was, Marko's real formatters are used (via ExceptionDisplayer) for
 * the display capability layered on top.
 *
 * BACKWARD COMPATIBILITY (verified): the public API — constructor
 * `new ErrorHandler($logger)`, and methods register()/logException() — is
 * completely unchanged. Every existing call site across this codebase
 * requires zero changes.
 */
final readonly class ErrorHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private bool $debug = false,
    ) {
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

        set_exception_handler(function (Throwable $exception): void {
            $this->handleUncaughtException($exception);
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

    /**
     * Handle a genuinely uncaught exception: log it (via the existing
     * LocalLogger-backed logException()), then delegate real display to
     * zoosper-errors' ExceptionDisplayer — this class itself has zero
     * knowledge of Marko or any other display implementation detail.
     */
    private function handleUncaughtException(Throwable $exception): void
    {
        $this->logException($exception);
        (new ExceptionDisplayer())->display($exception);
    }

    public function httpResponse(Throwable $exception, bool $api = false): Response
    {
        if ($api) {
            return Response::json([
                'success' => false,
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'Zoosper encountered an unexpected error.',
                ],
            ], 500);
        }

        if (!$this->debug) {
            return Response::html('<h1>500</h1><p>An unexpected error occurred. The details have been logged.</p>', 500);
        }

        try {
            return Response::html((new ExceptionDisplayer())->formatHtml($exception), 500);
        } catch (Throwable $displayFailure) {
            $this->logger->exception($displayFailure, ['component' => 'exception_displayer']);

            return Response::html('<h1>500</h1><p>An unexpected error occurred. The details have been logged.</p>', 500);
        }
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
