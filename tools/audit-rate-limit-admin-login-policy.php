<?php

declare(strict_types=1);

/**
 * Read-only audit for the admin.login report-only rate-limit policy foundation.
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
    'docs/development/rate-limit-admin-login-report-only-policy.md',
    'app/zoosper-core/config/rate_limit.php',
    'app/zoosper-core/src/Security/RateLimit/AdminRateLimitContextFactory.php',
    'tools/apply-rate-limit-admin-login-policy.php',
    'tools/audit-rate-limit-admin-login-policy.php',
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
if (($config['enabled'] ?? null) !== false) {
    $errors[] = 'rate_limit.php must remain disabled by default.';
}

if (($config['mode'] ?? null) !== 'report_only') {
    $errors[] = 'rate_limit.php must remain in report_only mode by default.';
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-policy.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-policy.log';

$report = [];
$report[] = '# Admin Login Rate Limit Policy Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Default enabled: ' . json_encode($config['enabled'] ?? null);
$report[] = 'Default mode: ' . json_encode($config['mode'] ?? null);
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
$log[] = 'Admin login rate-limit policy audit written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_DEFAULT_ENABLED ' . json_encode($config['enabled'] ?? null);
$log[] = 'RATE_LIMIT_DEFAULT_MODE ' . json_encode($config['mode'] ?? null);
$log[] = 'ADMIN_LOGIN_POLICY_PRESENT ' . ($adminPolicyPresent ? 'yes' : 'no');
$log[] = 'RATE_LIMIT_ADMIN_LOGIN_POLICY_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
