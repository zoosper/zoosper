<?php

declare(strict_types=1);

/**
 * Read-only audit for the Phase 1.39 database-backed rate limit store.
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
    'docs/development/rate-limit-database-store.md',
    'database/schema/rate_limit_buckets.sql',
    'app/zoosper-core/src/Security/RateLimit/DatabaseRateLimitStore.php',
    'app/zoosper-core/tests/Unit/Security/DatabaseRateLimitStoreTest.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required file missing: ' . $relative;
    }
}

$storePath = $root . DIRECTORY_SEPARATOR . 'app/zoosper-core/src/Security/RateLimit/DatabaseRateLimitStore.php';
$storeSource = is_file($storePath) ? (string) file_get_contents($storePath) : '';
foreach (['recordAttempt', 'reset', 'deleteExpired', 'ensureSchema'] as $needle) {
    if (! str_contains($storeSource, $needle)) {
        $errors[] = 'DatabaseRateLimitStore missing expected symbol: ' . $needle;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-database-store.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-database-store.log';

$report = [];
$report[] = '# Database Rate Limit Store Audit';
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
$log[] = 'Database rate limit store report written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_DATABASE_STORE_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
