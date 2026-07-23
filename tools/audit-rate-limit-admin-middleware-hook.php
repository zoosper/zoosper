<?php

declare(strict_types=1);

/** Read-only audit for the disabled-by-default admin middleware hook. */

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

$target = $root . '/app/zoosper-auth/config/admin_middleware.php';
$configPath = $root . '/app/zoosper-core/config/rate_limit.php';
$errors = [];
$source = is_file($target) ? (string) file_get_contents($target) : '';
$config = is_file($configPath) ? require $configPath : [];
if (! is_file($target)) {
    $errors[] = 'admin_middleware.php missing.';
}
if (! is_array($config)) {
    $errors[] = 'rate_limit.php did not return an array.';
    $config = [];
}
$present = str_contains($source, 'Phase 1.39 report-only rate-limit hook') || str_contains($source, 'RateLimitMiddlewareIntegration');
if (! $present) {
    $errors[] = 'Disabled-by-default admin middleware rate-limit hook not found.';
}
if (($config['enabled'] ?? null) !== false) {
    $errors[] = 'rate_limit.php must remain disabled by default.';
}
if (($config['mode'] ?? null) !== 'report_only') {
    $errors[] = 'rate_limit.php must remain report_only by default.';
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-middleware-hook.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-middleware-hook.log';
$report = [];
$report[] = '# Rate Limit Admin Middleware Hook Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Hook present: ' . ($present ? 'yes' : 'no');
$report[] = 'Default enabled: ' . json_encode($config['enabled'] ?? null);
$report[] = 'Default mode: ' . json_encode($config['mode'] ?? null);
if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}
file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Rate limit admin middleware hook audit written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_ADMIN_MIDDLEWARE_HOOK_ERRORS ' . count($errors);
$log[] = 'RATE_LIMIT_ADMIN_MIDDLEWARE_HOOK_PRESENT ' . ($present ? 'yes' : 'no');
$log[] = 'RATE_LIMIT_DEFAULT_ENABLED ' . json_encode($config['enabled'] ?? null);
$log[] = 'RATE_LIMIT_DEFAULT_MODE ' . json_encode($config['mode'] ?? null);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);
echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
