<?php

declare(strict_types=1);

/**
 * Read-only audit for the Phase 1.39 rate limit policy seam.
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
    'docs/development/rate-limit-policy-and-enforcement-seam.md',
    'app/zoosper-core/src/Security/RateLimit/RateLimitEnforcer.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitPolicy.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitPolicyResolverInterface.php',
    'app/zoosper-core/src/Security/RateLimit/StaticRateLimitPolicyResolver.php',
    'app/zoosper-core/tests/Unit/Security/RateLimitPolicySeamTest.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required file missing: ' . $relative;
    }
}

$enforcer = $root . DIRECTORY_SEPARATOR . 'app/zoosper-core/src/Security/RateLimit/RateLimitEnforcer.php';
$enforcerSource = is_file($enforcer) ? (string) file_get_contents($enforcer) : '';
foreach (['RateLimitStoreInterface', 'RateLimitPolicy', 'RateLimitDecision', 'recordAttempt'] as $needle) {
    if (! str_contains($enforcerSource, $needle)) {
        $errors[] = 'RateLimitEnforcer missing expected token: ' . $needle;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-policy-seam.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-policy-seam.log';

$report = [];
$report[] = '# Rate Limit Policy Seam Audit';
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
$log[] = 'Rate limit policy seam report written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_POLICY_SEAM_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
