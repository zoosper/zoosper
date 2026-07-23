<?php

declare(strict_types=1);

/**
 * Read-only audit for disabled-by-default report-only rate-limit wiring scaffold.
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
    'docs/development/rate-limit-report-only-wiring-strategy.md',
    'app/zoosper-core/src/Security/RateLimit/RateLimitReportOnlyMiddlewareFactory.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitMiddlewareIntegration.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitRuntimeConfig.php',
    'app/zoosper-core/src/Security/RateLimit/ReportOnlyRateLimitMiddleware.php',
    'app/zoosper-core/config/rate_limit.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required file missing: ' . $relative;
    }
}

$integrationPath = $root . DIRECTORY_SEPARATOR . 'app/zoosper-core/src/Security/RateLimit/RateLimitMiddlewareIntegration.php';
$integrationSource = is_file($integrationPath) ? (string) file_get_contents($integrationPath) : '';
foreach (['! $config->enabled', 'isReportOnly', 'factory->create'] as $needle) {
    if (! str_contains($integrationSource, $needle)) {
        $errors[] = 'RateLimitMiddlewareIntegration missing expected token: ' . $needle;
    }
}

$configPath = $root . DIRECTORY_SEPARATOR . 'app/zoosper-core/config/rate_limit.php';
$config = is_file($configPath) ? require $configPath : [];
if (! is_array($config) || ($config['enabled'] ?? null) !== false) {
    $errors[] = 'rate_limit.php must remain disabled by default.';
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-report-only-wiring.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-report-only-wiring.log';

$report = [];
$report[] = '# Rate Limit Report-only Wiring Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Default enabled: ' . json_encode(is_array($config) ? ($config['enabled'] ?? null) : null);
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
$log[] = 'Rate limit report-only wiring audit written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_REPORT_ONLY_WIRING_ERRORS ' . count($errors);
$log[] = 'RATE_LIMIT_DEFAULT_ENABLED ' . json_encode(is_array($config) ? ($config['enabled'] ?? null) : null);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
