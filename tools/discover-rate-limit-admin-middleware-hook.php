<?php

declare(strict_types=1);

/**
 * Discover likely admin middleware hook points for report-only rate limiting.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

$sourceDir = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-middleware-hook-source';
if (! is_dir($sourceDir) && ! mkdir($sourceDir, 0775, true) && ! is_dir($sourceDir)) {
    fwrite(STDERR, 'Unable to create source output directory: ' . $sourceDir . PHP_EOL);
    exit(1);
}

$candidates = [
    'app/zoosper-auth/config/admin_middleware.php',
    'app/zoosper-auth/config/services.php',
    'app/zoosper-core/src/Http/Middleware/ModuleAdminMiddlewareLoader.php',
    'app/zoosper-core/src/Http/Middleware/MiddlewarePipeline.php',
    'app/zoosper-core/src/Bootstrap/ApplicationFactory.php',
];

$errors = [];
$signals = [];
foreach ($candidates as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $exists = is_file($path);
    $contents = $exists ? (string) file_get_contents($path) : '';
    if (! $exists) {
        $errors[] = 'Candidate missing: ' . $relative;
    } else {
        $safeName = str_replace(['/', '\\'], '__', $relative) . '.txt';
        file_put_contents($sourceDir . DIRECTORY_SEPARATOR . $safeName, $contents);
    }

    $signals[$relative] = [
        'exists' => $exists,
        'contains_return_array' => str_contains($contents, 'return ['),
        'contains_authentication_middleware' => str_contains($contents, 'AuthenticationMiddleware'),
        'contains_csrf' => stripos($contents, 'csrf') !== false,
        'contains_middleware' => stripos($contents, 'middleware') !== false,
        'contains_rate_limit' => stripos($contents, 'RateLimit') !== false || stripos($contents, 'rate_limit') !== false,
    ];
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-middleware-hook.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-middleware-hook.log';

$report = [];
$report[] = '# Rate Limit Admin Middleware Hook Discovery';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Source output directory: ' . $sourceDir;
$report[] = '';
$report[] = '## Candidate signals';
foreach ($signals as $relative => $items) {
    $report[] = '';
    $report[] = '### ' . $relative;
    foreach ($items as $name => $value) {
        $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
    }
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
$log[] = 'Rate limit admin middleware hook discovery written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_ADMIN_HOOK_DISCOVERY_ERRORS ' . count($errors);
$log[] = 'SOURCE_DIR ' . $sourceDir;
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit(0);
