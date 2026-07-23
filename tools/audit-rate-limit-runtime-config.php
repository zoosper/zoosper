<?php

declare(strict_types=1);

/**
 * Read-only audit for rate-limit runtime config and report sink readiness.
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
    'docs/development/rate-limit-runtime-config-and-reporting.md',
    'app/zoosper-core/config/rate_limit.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitRuntimeConfig.php',
    'app/zoosper-core/src/Security/RateLimit/FileRateLimitReportSink.php',
    'app/zoosper-core/src/Security/RateLimit/ReportOnlyRateLimitMiddleware.php',
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

if (($config['enabled'] ?? null) !== false) {
    $errors[] = 'rate_limit.php must be disabled by default.';
}

if (($config['mode'] ?? null) !== 'report_only') {
    $errors[] = 'rate_limit.php must default to report_only mode.';
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-runtime-config.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-runtime-config.log';

$report = [];
$report[] = '# Rate Limit Runtime Config Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Enabled default: ' . json_encode($config['enabled'] ?? null);
$report[] = 'Mode default: ' . json_encode($config['mode'] ?? null);
$report[] = 'Policy count: ' . (is_array($config['policies'] ?? null) ? count($config['policies']) : 0);
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
$log[] = 'Rate limit runtime config audit written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_RUNTIME_CONFIG_ERRORS ' . count($errors);
$log[] = 'RATE_LIMIT_DEFAULT_ENABLED ' . json_encode($config['enabled'] ?? null);
$log[] = 'RATE_LIMIT_DEFAULT_MODE ' . json_encode($config['mode'] ?? null);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
