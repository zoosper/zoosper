<?php

declare(strict_types=1);

/**
 * Read-only audit for the report-only rate-limit middleware adapter.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$required = [
    'docs/development/rate-limit-report-only-middleware.md',
    'app/zoosper-core/src/Security/RateLimit/RateLimitReportEvent.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitReportSinkInterface.php',
    'app/zoosper-core/src/Security/RateLimit/InMemoryRateLimitReportSink.php',
    'app/zoosper-core/src/Security/RateLimit/ReportOnlyRateLimitMiddleware.php',
    'app/zoosper-core/tests/Unit/Security/RateLimitReportOnlyMiddlewareTest.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required file missing: ' . $relative;
    }
}

$middlewarePath = $root . DIRECTORY_SEPARATOR . 'app/zoosper-core/src/Security/RateLimit/ReportOnlyRateLimitMiddleware.php';
$middlewareSource = is_file($middlewarePath) ? (string) file_get_contents($middlewarePath) : '';
foreach (['RateLimitGuard', 'RateLimitReportSinkInterface', 'RateLimitContext', 'RateLimitDecision', 'record'] as $needle) {
    if (! str_contains($middlewareSource, $needle)) {
        $errors[] = 'ReportOnlyRateLimitMiddleware missing expected token: ' . $needle;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-report-only-middleware.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-report-only-middleware.log';

$report = [];
$report[] = '# Rate Limit Report-only Middleware Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Required files';
foreach ($required as $relative) {
    $report[] = '- ' . $relative . ': ' . (is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)) ? 'exists' : 'missing');
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Rate limit report-only middleware audit written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_REPORT_ONLY_MIDDLEWARE_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
