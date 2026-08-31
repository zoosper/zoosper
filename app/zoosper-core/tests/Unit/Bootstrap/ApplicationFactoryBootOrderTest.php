<?php

declare(strict_types=1);

use Zoosper\Core\Bootstrap\ApplicationFactory;

/**
 * CORRECTNESS/SECURITY REGRESSION TEST — proves ApplicationFactory::create()
 * registers the ErrorHandler BEFORE attempting the database connection, so
 * a real connection failure during boot (arguably the single most common
 * production incident) is captured by our own error handler rather than by
 * whatever `display_errors` happens to be configured — avoiding potential
 * stack-trace/credential leakage.
 *
 * TESTING APPROACH, STATED HONESTLY: genuinely proving "handler X was
 * active at the moment exception Y was thrown" via global
 * set_exception_handler() state is fragile to test directly — handler
 * state is global, mutable, and sensitive to test execution order, and
 * ApplicationFactory::create() does not itself restore handlers on
 * failure (only some test harnesses, like FrontendBootTest, explicitly do
 * that on SUCCESS). Rather than build a fragile test around global
 * handler-probing, this test verifies the actual fix directly via
 * source-order inspection: does `$errorHandler->register()` appear before
 * the ConnectionFactory instantiation in the real create() method's
 * source? This is a precise, reliable way to verify call ORDER when the
 * two calls don't have an easily-observable, side-effect-based way to
 * distinguish ordering.
 *
 * The existing FrontendBootTest.php already covers the happy-path
 * regression (ApplicationFactory::create() still boots successfully with
 * this reorder) — not duplicated here.
 *
 * File placement: app/zoosper-core/tests/Unit/Bootstrap/ApplicationFactoryBootOrderTest.php.
 * This test does not need to resolve a repo-root path itself — it locates
 * ApplicationFactory's source file via ReflectionMethod::getFileName(),
 * not dirname()-based path arithmetic.
 */
it('registers the ErrorHandler before creating the database connection', function (): void {
    $reflection = new ReflectionMethod(ApplicationFactory::class, 'create');
    $filename = $reflection->getFileName();
    $startLine = $reflection->getStartLine();
    $endLine = $reflection->getEndLine();

    expect($filename)->not->toBeFalse();

    $lines = file($filename);
    expect($lines)->not->toBeFalse();

    $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

    $errorHandlerRegisterPos = strpos($methodSource, 'errorHandler->register()');
    $connectionFactoryPos = strpos($methodSource, 'new ConnectionFactory(');

    expect($errorHandlerRegisterPos)->not->toBeFalse();
    expect($connectionFactoryPos)->not->toBeFalse();

    // The actual fix under test: ErrorHandler::register() must appear
    // BEFORE the ConnectionFactory instantiation in the source.
    expect($errorHandlerRegisterPos)->toBeLessThan($connectionFactoryPos);
});










