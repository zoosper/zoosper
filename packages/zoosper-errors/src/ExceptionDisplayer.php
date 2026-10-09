<?php

declare(strict_types=1);

namespace Zoosper\Errors;

use Marko\Errors\ErrorReport;
use Marko\Errors\Severity;
use Marko\ErrorsSimple\CodeSnippetExtractor;
use Marko\ErrorsSimple\Environment;
use Marko\ErrorsSimple\Formatters\BasicHtmlFormatter;
use Marko\ErrorsSimple\Formatters\TextFormatter;
use Marko\Clock\SystemClock;
use Psr\Clock\ClockInterface;
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
    public function __construct(
        private ?ClockInterface $clock = null,
        private ?Environment $environment = null,
    ) {}
    public function formatHtml(Throwable $exception): string
    {
        $report = $this->report($exception);
        $environment = $this->environment ?? new Environment(envVars: ['APP_ENV' => 'development']);
        $extractor = new CodeSnippetExtractor();
        $formatter = new BasicHtmlFormatter($environment, $extractor);

        return $formatter->format($report);
    }

    public function display(Throwable $exception): void
    {
        $report = $this->report($exception);
        $environment = $this->environment ?? new Environment(envVars: ['APP_ENV' => 'development']);
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

    private function report(Throwable $exception): ErrorReport
    {
        $clock = $this->clock ?? new SystemClock();

        return ErrorReport::fromThrowable($exception, Severity::Error, $clock->now());
    }
}











