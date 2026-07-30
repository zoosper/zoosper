<?php

declare(strict_types=1);

use Marko\Core\Exceptions\MarkoException;
use Marko\Errors\ErrorReport;
use Marko\Errors\Severity;
use Zoosper\Errors\ZoosperException;

/**
 * FRAMEWORK INTEGRATION REGRESSION TEST — proves ZoosperException genuinely
 * integrates with marko/core and marko/errors, while proving every existing
 * Zoosper-specific behaviour (context()/suggestion()/docsUrl()/details())
 * continues to work identically.
 *
 * PACKAGE EXTRACTION (2026-07-30): moved from
 * app/zoosper-core/tests/Unit/Exception/ZoosperExceptionMarkoIntegrationTest.php
 * into this package's own tests/Unit/ folder — the `use Zoosper\Core\
 * Exception\ZoosperException;` import updated to `use Zoosper\Errors\
 * ZoosperException;` to match the class's new home; every assertion is
 * otherwise unchanged.
 */
it('is recognised as a genuine MarkoException by instanceof (real framework interoperability)', function (): void {
    $exception = new ZoosperException(message: 'Something failed.');

    expect($exception)->toBeInstanceOf(MarkoException::class);
});

it('preserves every existing Zoosper-specific accessor exactly as before (backward compatibility)', function (): void {
    $exception = new ZoosperException(
        message: 'Unable to create thing.',
        context: 'While creating a thing.',
        suggestion: 'Check the thing configuration.',
        docsUrl: 'docs/things.md',
        details: ['thing_id' => 42],
    );

    expect($exception->getMessage())->toBe('Unable to create thing.');
    expect($exception->context())->toBe('While creating a thing.');
    expect($exception->suggestion())->toBe('Check the thing configuration.');
    expect($exception->docsUrl())->toBe('docs/things.md');
    expect($exception->details())->toBe(['thing_id' => 42]);
});

it('is correctly recognised by the REAL Marko\Errors\ErrorReport::fromThrowable(), populating context/suggestion automatically', function (): void {
    $exception = new ZoosperException(
        message: 'Config file missing.',
        context: 'Loading config/example.php',
        suggestion: 'Create the missing config file.',
    );

    $report = ErrorReport::fromThrowable($exception, Severity::Error);

    expect($report->context)->toBe('Loading config/example.php');
    expect($report->suggestion)->toBe('Create the missing config file.');
    expect($report->message)->toBe('Config file missing.');
    expect($report->severity)->toBe(Severity::Error);
});

it('still supports being constructed with only a message, matching the previous default-argument behaviour', function (): void {
    $exception = new ZoosperException('Just a message.');

    expect($exception->getMessage())->toBe('Just a message.');
    expect($exception->context())->toBe('');
    expect($exception->suggestion())->toBe('');
    expect($exception->docsUrl())->toBeNull();
    expect($exception->details())->toBe([]);
});

it('still supports a wrapped previous exception, matching the previous behaviour', function (): void {
    $previous = new \RuntimeException('Original cause.');
    $exception = new ZoosperException(message: 'Wrapper message.', previous: $previous);

    expect($exception->getPrevious())->toBe($previous);
});
