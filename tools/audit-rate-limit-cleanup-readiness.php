<?php

declare(strict_types=1);

/**
 * Read-only audit for rate limit schema and cleanup readiness.
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
    'docs/development/rate-limit-cleanup-and-schema.md',
    'app/zoosper-core/config/db_schema.php',
    'tools/cleanup-expired-rate-limit-buckets.php',
    'tools/audit-rate-limit-cleanup-readiness.php',
    'app/zoosper-core/src/Security/RateLimit/DatabaseRateLimitStore.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required file missing: ' . $relative;
    }
}

$schemaPath = $root . DIRECTORY_SEPARATOR . 'app/zoosper-core/config/db_schema.php';
$schemaSource = is_file($schemaPath) ? (string) file_get_contents($schemaPath) : '';
foreach (['rate_limit_buckets', 'identity_hash', 'window_ends_at'] as $needle) {
    if (! str_contains($schemaSource, $needle)) {
        $errors[] = 'db_schema.php missing expected rate limit schema token: ' . $needle;
    }
}

$cleanupPath = $root . DIRECTORY_SEPARATOR . 'tools/cleanup-expired-rate-limit-buckets.php';
$cleanupSource = is_file($cleanupPath) ? (string) file_get_contents($cleanupPath) : '';
foreach (['--apply', '--database=', 'deleteExpired'] as $needle) {
    if (! str_contains($cleanupSource, $needle)) {
        $errors[] = 'cleanup command missing expected token: ' . $needle;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-cleanup-readiness.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-cleanup-readiness.log';

$report = [];
$report[] = '# Rate Limit Cleanup Readiness Audit';
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
$log[] = 'Rate limit cleanup readiness report written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_CLEANUP_READINESS_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
