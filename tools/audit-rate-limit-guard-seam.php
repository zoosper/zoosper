<?php

declare(strict_types=1);

/**
 * Read-only audit for the Phase 1.39 rate limit guard seam.
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
    'docs/development/rate-limit-guard-and-identity.md',
    'app/zoosper-core/src/Security/RateLimit/RateLimitContext.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitIdentityHasher.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitGuard.php',
    'app/zoosper-core/tests/Unit/Security/RateLimitGuardSeamTest.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required file missing: ' . $relative;
    }
}

$guardPath = $root . DIRECTORY_SEPARATOR . 'app/zoosper-core/src/Security/RateLimit/RateLimitGuard.php';
$guardSource = is_file($guardPath) ? (string) file_get_contents($guardPath) : '';
foreach (['RateLimitPolicyResolverInterface', 'RateLimitEnforcer', 'RateLimitContext', 'RateLimitDecision'] as $needle) {
    if (! str_contains($guardSource, $needle)) {
        $errors[] = 'RateLimitGuard missing expected token: ' . $needle;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-guard-seam.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-guard-seam.log';

$report = [];
$report[] = '# Rate Limit Guard Seam Audit';
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
$log[] = 'Rate limit guard seam report written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_GUARD_SEAM_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
