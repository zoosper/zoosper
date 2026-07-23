<?php

declare(strict_types=1);

/** Full closeout audit for Phase 1.39 rate limiting. */

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
    'app/zoosper-core/src/Security/RateLimit/RateLimitRule.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitDecision.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitStoreInterface.php',
    'app/zoosper-core/src/Security/RateLimit/DatabaseRateLimitStore.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitRuntimeConfig.php',
    'app/zoosper-core/src/Security/RateLimit/ReportOnlyRateLimitMiddleware.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitMiddlewareIntegration.php',
    'app/zoosper-core/src/Security/RateLimit/AdminRateLimitContextFactory.php',
    'app/zoosper-core/config/rate_limit.php',
    'app/zoosper-auth/config/admin_middleware.php',
    'tools/smoke-rate-limit-admin-login-report-only.php',
    'tools/audit-rate-limit-admin-middleware-hook.php',
    'docs/development/rate-limit-phase-139-closeout.md',
    'docs/architecture/adr-rate-limiting-report-only-to-enforcement.md',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required closeout file missing: ' . $relative;
    }
}
$config = is_file($root . '/app/zoosper-core/config/rate_limit.php') ? require $root . '/app/zoosper-core/config/rate_limit.php' : [];
if (! is_array($config)) {
    $errors[] = 'rate_limit.php did not return an array.';
    $config = [];
}
if (($config['enabled'] ?? null) !== false) {
    $errors[] = 'rate_limit.php must remain disabled by default.';
}
if (($config['mode'] ?? null) !== 'report_only') {
    $errors[] = 'rate_limit.php must be report_only by default.';
}
if (! isset(($config['policies'] ?? [])['admin.login'])) {
    $errors[] = 'admin.login policy missing.';
}
$adminSource = is_file($root . '/app/zoosper-auth/config/admin_middleware.php') ? (string) file_get_contents($root . '/app/zoosper-auth/config/admin_middleware.php') : '';
if (! str_contains($adminSource, 'Phase 1.39 report-only rate-limit hook') && ! str_contains($adminSource, 'RateLimitMiddlewareIntegration')) {
    $errors[] = 'admin middleware hook missing.';
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-phase-139-closeout.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-phase-139-closeout.log';
$report = [];
$report[] = '# Phase 1.39 Rate Limiting Closeout Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Required files: ' . count($required);
$report[] = 'Default enabled: ' . json_encode($config['enabled'] ?? null);
$report[] = 'Default mode: ' . json_encode($config['mode'] ?? null);
$report[] = 'Admin login policy present: ' . (isset(($config['policies'] ?? [])['admin.login']) ? 'yes' : 'no');
if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}
file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Phase 1.39 rate-limit closeout audit written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_PHASE_139_CLOSEOUT_ERRORS ' . count($errors);
$log[] = 'RATE_LIMIT_DEFAULT_ENABLED ' . json_encode($config['enabled'] ?? null);
$log[] = 'RATE_LIMIT_DEFAULT_MODE ' . json_encode($config['mode'] ?? null);
$log[] = 'ADMIN_LOGIN_POLICY_PRESENT ' . (isset(($config['policies'] ?? [])['admin.login']) ? 'yes' : 'no');
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);
echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
