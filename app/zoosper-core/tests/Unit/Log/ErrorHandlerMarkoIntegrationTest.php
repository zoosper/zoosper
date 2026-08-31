<?php

declare(strict_types=1);

/**
 * BUG FIX + FRAMEWORK INTEGRATION REGRESSION TEST — proves two things
 * together, since they were fixed in the same change:
 *
 * 1. THE BUG FIX: ErrorHandler::register() previously never called
 *    set_exception_handler() at all — genuinely uncaught exceptions never
 *    reached the richer logException() path (redaction, ZoosperException
 *    context/suggestion/details extraction). This test proves an uncaught
 *    exception now reaches that real path correctly.
 *
 * 2. THE MARKO INTEGRATION (via delegation): the actual on-screen/CLI
 *    output for that same uncaught exception is now produced by
 *    zoosper-errors' ExceptionDisplayer, which itself uses Marko's REAL,
 *    installed TextFormatter (marko/errors-simple). ErrorHandler.php
 *    itself has ZERO knowledge of Marko — see ExceptionDisplayer's own
 *    tests (packages/zoosper-errors/tests/Unit/ExceptionDisplayerTest.php)
 *    for a more direct, in-process proof of that delegation working
 *    correctly. This test proves the end-to-end wiring through
 *    ErrorHandler still produces the same observable result.
 *
 * ARCHITECTURAL BOUNDARY FIX (2026-07-30, same day): ErrorHandler.php
 * previously imported 6 Marko\* classes directly. That logic moved to
 * Zoosper\Errors\ExceptionDisplayer (owned by the zoosper-errors package,
 * which already owns the real dependency on Marko). ErrorHandler now only
 * calls (new ExceptionDisplayer())->display($exception) — zoosper-core's
 * own composer.json no longer needs marko/errors or marko/errors-simple
 * declared directly at all. This test's assertions are UNCHANGED from
 * before this refactor, since the actual observable behaviour (what gets
 * logged, what gets printed) is identical — only where the Marko-specific
 * code lives changed.
 *
 * TESTING APPROACH: ErrorHandler::register() mutates PHP's global
 * exception/error handler state, which cannot be safely tested within the
 * same Pest process (state would leak into every other test). This test
 * spawns a real, isolated `php` subprocess for each case, executing a real
 * temporary .php file (NOT `php -r` — confirmed via careful, isolated
 * diagnostic testing earlier this session that PHP's `-r` flag has
 * different, non-standard set_exception_handler() behaviour in its
 * special "Command line code" eval context; this has no bearing on real
 * application code, which always runs from real .php files).
 *
 * File placement: app/zoosper-core/tests/Unit/Log/ErrorHandlerMarkoIntegrationTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
function errorHandlerTestRunSubprocess(string $basePath, string $logFilePath): array
{
    $scriptTemplate = <<<'PHP'
<?php

require %s;

$logger = new \Zoosper\Logger\Driver\LocalLogger(%s, true);
$handler = new \Zoosper\Core\Error\ErrorHandler($logger);
$handler->register();

throw new \RuntimeException('Deliberate test exception for ErrorHandler coverage.');
PHP;

    $bootstrapPath = var_export($basePath . '/bootstrap/autoload.php', true);
    $logFilePathExported = var_export($logFilePath, true);
    $scriptContents = sprintf($scriptTemplate, $bootstrapPath, $logFilePathExported);

    $scriptPath = sys_get_temp_dir() . '/zoosper-error-handler-test-script-' . bin2hex(random_bytes(6)) . '.php';
    file_put_contents($scriptPath, $scriptContents);

    $process = proc_open(
        [PHP_BINARY, $scriptPath],
        [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
    );

    $stdout = '';
    $stderr = '';
    if (is_resource($process)) {
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
    } else {
        $exitCode = 1;
    }

    @unlink($scriptPath);

    return [
        'stdout' => trim($stdout . ($stderr ? "\n" . $stderr : '')),
        'exitCode' => $exitCode,
    ];
}

it('BUG FIX: logs a genuinely uncaught exception to the real log file (previously never happened at all)', function (): void {
    $basePath = dirname(__DIR__, 5);
    $logFilePath = sys_get_temp_dir() . '/zoosper-error-handler-test-' . bin2hex(random_bytes(6)) . '.log';

    errorHandlerTestRunSubprocess($basePath, $logFilePath);

    expect(is_file($logFilePath))->toBeTrue();

    $logContents = (string) file_get_contents($logFilePath);

    expect($logContents)->toContain('Deliberate test exception for ErrorHandler coverage.');
    expect($logContents)->toContain('ERROR');

    @unlink($logFilePath);
});

it('MARKO INTEGRATION (via delegation): displays the uncaught exception using the REAL Marko TextFormatter output shape', function (): void {
    $basePath = dirname(__DIR__, 5);
    $logFilePath = sys_get_temp_dir() . '/zoosper-error-handler-test-' . bin2hex(random_bytes(6)) . '.log';

    $result = errorHandlerTestRunSubprocess($basePath, $logFilePath);

    expect($result['stdout'])->toContain('RuntimeException');
    expect($result['stdout'])->toContain('Deliberate test exception for ErrorHandler coverage.');
    expect($result['stdout'])->toContain('Stack Trace:');

    @unlink($logFilePath);
});

it('logs AND displays together for the same exception (both halves of the fix work in the same real run)', function (): void {
    $basePath = dirname(__DIR__, 5);
    $logFilePath = sys_get_temp_dir() . '/zoosper-error-handler-test-' . bin2hex(random_bytes(6)) . '.log';

    $result = errorHandlerTestRunSubprocess($basePath, $logFilePath);

    expect(is_file($logFilePath))->toBeTrue();
    expect((string) file_get_contents($logFilePath))->toContain('Deliberate test exception for ErrorHandler coverage.');
    expect($result['stdout'])->toContain('Deliberate test exception for ErrorHandler coverage.');

    @unlink($logFilePath);
});










