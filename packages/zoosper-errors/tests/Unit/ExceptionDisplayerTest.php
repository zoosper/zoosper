<?php

declare(strict_types=1);

use Zoosper\Errors\ExceptionDisplayer;

/**
 * ARCHITECTURAL BOUNDARY REGRESSION TEST — proves ExceptionDisplayer
 * correctly delegates to Marko's REAL, installed formatters.
 *
 * Unlike ErrorHandlerMarkoIntegrationTest (in zoosper-core, which must use
 * a real subprocess because ErrorHandler::register() mutates PHP's global
 * exception/error handler state), ExceptionDisplayer::display() has NO
 * global state of its own at all — it can be tested safely, simply, and
 * quickly in-process using plain output buffering.
 *
 * File placement: packages/zoosper-errors/tests/Unit/ExceptionDisplayerTest.php
 */
it('produces the REAL Marko TextFormatter output shape for CLI output', function (): void {
    $exception = new RuntimeException('Deliberate test exception for ExceptionDisplayer coverage.');

    ob_start();
    (new ExceptionDisplayer())->display($exception);
    $output = ob_get_clean();

    // Confirms the REAL Marko\ErrorsSimple\Formatters\TextFormatter
    // genuinely ran and produced its own recognisable output shape
    // (exception class name, message, and its literal "Stack Trace:"
    // section header) — not a hand-built reimplementation of similar text.
    expect($output)->toContain('RuntimeException');
    expect($output)->toContain('Deliberate test exception for ExceptionDisplayer coverage.');
    expect($output)->toContain('Stack Trace:');
});

it('includes context and suggestion for a ZoosperException, via the REAL Marko formatter', function (): void {
    $exception = new \Zoosper\Errors\ZoosperException(
        message: 'Config file missing.',
        context: 'Loading config/example.php',
        suggestion: 'Create the missing config file.',
    );

    ob_start();
    (new ExceptionDisplayer())->display($exception);
    $output = ob_get_clean();

    expect($output)->toContain('Config file missing.');
    expect($output)->toContain('Loading config/example.php');
    expect($output)->toContain('Create the missing config file.');
});
