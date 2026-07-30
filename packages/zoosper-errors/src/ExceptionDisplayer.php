<?php

declare(strict_types=1);

namespace Zoosper\Errors;

use Marko\Errors\ErrorReport;
use Marko\Errors\Severity;
use Marko\ErrorsSimple\CodeSnippetExtractor;
use Marko\ErrorsSimple\Environment;
use Marko\ErrorsSimple\Formatters\BasicHtmlFormatter;
use Marko\ErrorsSimple\Formatters\TextFormatter;
use Throwable;

/**
 * Displays an exception using Marko's real, installed formatters
 * (marko/errors + marko/errors-simple).
 *
 * ARCHITECTURAL BOUNDARY (2026-07-30): this class exists specifically so
 * that Zoosper\Core\Log\ErrorHandler — and any other future Zoosper
 * consumer — never needs to import a single Marko\* class directly, or
 * declare marko/errors / marko/errors-simple in its own composer.json.
 * zoosper-core should not need to know Zoosper's error display is
 * Marko-based at all; it should only ever depend on zoosper/errors' own
 * public API surface. This class IS that public API surface for display.
 * zoosper/errors owns the real, direct dependency on marko/errors and
 * marko/errors-simple (declared in ITS OWN composer.json's `require` —
 * not `require-dev` — since this is now load-bearing production code, not
 * merely a test-time dependency).
 *
 * This follows the exact same "depend on an interface/boundary owned by
 * the right module, not the concrete implementation" pattern already
 * applied elsewhere in this codebase (e.g. Zoosper\Core\Site\
 * SiteLookupInterface, Zoosper\Auth\Layout\AdminLayoutRendererInterface).
 *
 * If Zoosper ever wants to change the underlying display implementation
 * away from Marko in the future, this is the one class that would need to
 * change — every consumer calling ->display() is completely unaffected.
 */
final readonly class ExceptionDisplayer
{
    public function display(Throwable $exception): void
    {
        $report = ErrorReport::fromThrowable($exception, Severity::Error);
        $environment = new Environment();
        $extractor = new CodeSnippetExtractor();

        if (PHP_SAPI === 'cli') {
            $formatter = new TextFormatter($environment, $extractor);
            echo $formatter->format($report);

            return;
        }

        if (!headers_sent()) {
            http_response_code(500);
        }

        $formatter = new BasicHtmlFormatter($environment, $extractor);
        echo $formatter->format($report);
    }
}
