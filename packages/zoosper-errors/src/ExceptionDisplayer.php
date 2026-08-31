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
 * Serves as an architectural boundary so consumers do not need to import
 * Marko error classes directly.
 */
final readonly class ExceptionDisplayer
{
    public function formatHtml(Throwable $exception): string
    {
        $report = ErrorReport::fromThrowable($exception, Severity::Error);
        $environment = new Environment();
        $extractor = new CodeSnippetExtractor();
        $formatter = new BasicHtmlFormatter($environment, $extractor);

        return $formatter->format($report);
    }

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

        echo $this->formatHtml($exception);
    }
}
