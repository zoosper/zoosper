<?php

declare(strict_types=1);

/**
 * Read-only audit for admin login report-only smoke readiness.
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
    'docs/development/rate-limit-admin-login-report-only-smoke.md',
    'docs/development/rate-limit-admin-login-pre-live-hook-closeout.md',
    'tools/smoke-rate-limit-admin-login-report-only.php',
    'app/zoosper-core/config/rate_limit.php',
    'app/zoosper-core/src/Security/RateLimit/AdminRateLimitContextFactory.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitMiddlewareIntegration.php',
    'app/zoosper-core/src/Security/RateLimit/FileRateLimitReportSink.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required file missing: ' . $relative;
    }
}

$configPath = $root . DIRECTORY_SEPARATOR . 'app/zoosper-core/config/rate_limit.php';
$config = is_file($configPath) ? require $configPath : [];
if (! is_array($config)) {
    $errors[] = 'rate_limit.php did not return an array.';
    $config = [];
}

$adminPolicyPresent = isset(($config['policies'] ?? [])['admin.login']);

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-smoke-readiness.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-smoke-readiness.log';

$report = [];
$report[] = '# Admin Login Report-only Smoke Readiness Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Admin login policy present: ' . ($adminPolicyPresent ? 'yes' : 'no');
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
$log[] = 'Admin login report-only smoke readiness written to: ' . $reportPath;
$log[] = 'ADMIN_LOGIN_SMOKE_READINESS_ERRORS ' . count($errors);
$log[] = 'ADMIN_LOGIN_POLICY_PRESENT ' . ($adminPolicyPresent ? 'yes' : 'no');
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
