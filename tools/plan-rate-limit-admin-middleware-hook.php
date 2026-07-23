<?php

declare(strict_types=1);

/**
 * Plan a guarded report-only rate-limit hook for admin middleware.
 *
 * This is intentionally read-only unless --apply is supplied. Apply mode is
 * currently conservative and only writes the generated plan; it does not mutate
 * live middleware config until a source-specific pattern is approved.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
$apply = false;
foreach ($argv as $argument) {
    if ($argument === '--apply') {
        $apply = true;
        continue;
    }
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$targetRelative = 'app/zoosper-auth/config/admin_middleware.php';
$targetPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $targetRelative);
$errors = [];
$actions = [];
$pattern = 'unknown';
$canPatchLater = false;

if (! is_file($targetPath)) {
    $errors[] = 'Target admin middleware config does not exist: ' . $targetRelative;
} else {
    $source = (string) file_get_contents($targetPath);
    if (str_contains($source, 'RateLimitMiddlewareIntegration') || str_contains($source, 'ReportOnlyRateLimitMiddleware')) {
        $pattern = 'already mentions rate-limit middleware';
        $actions[] = 'No new hook required; target already mentions rate-limit middleware.';
    } elseif (str_contains($source, 'return [') && str_contains($source, 'AuthenticationMiddleware')) {
        $pattern = 'return-array-with-authentication-middleware';
        $canPatchLater = true;
        $actions[] = 'A future source-specific patch can add a disabled-by-default report-only middleware entry to the returned middleware list.';
    } elseif (str_contains($source, 'return [') || str_contains($source, 'return array')) {
        $pattern = 'return-array';
        $canPatchLater = true;
        $actions[] = 'A future source-specific patch may be possible after inspecting exact array shape.';
    } else {
        $pattern = 'unrecognised';
        $errors[] = 'Target exists but the source shape is not recognised as a simple middleware return array.';
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-middleware-hook-plan.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-middleware-hook-plan.log';

$report = [];
$report[] = '# Rate Limit Admin Middleware Hook Plan';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$report[] = 'Target: ' . $targetRelative;
$report[] = 'Pattern: ' . $pattern;
$report[] = 'Can patch later: ' . ($canPatchLater ? 'yes' : 'no');
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Proposed next patch shape';
$report[] = '- Keep `app/zoosper-core/config/rate_limit.php` disabled by default.';
$report[] = '- Add report-only middleware only through `RateLimitMiddlewareIntegration`.';
$report[] = '- Do not wire enforce mode in this phase.';
$report[] = '- Preserve AuthenticationMiddleware and CsrfMiddleware ordering.';

if ($actions !== []) {
    $report[] = '';
    $report[] = '## Actions';
    foreach ($actions as $action) {
        $report[] = '- ' . $action;
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
$log[] = 'Rate limit admin middleware hook plan written to: ' . $reportPath;
$log[] = 'MODE ' . ($apply ? 'apply' : 'dry-run');
$log[] = 'PATTERN ' . $pattern;
$log[] = 'CAN_PATCH_LATER ' . ($canPatchLater ? 'yes' : 'no');
$log[] = 'RATE_LIMIT_ADMIN_HOOK_PLAN_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
