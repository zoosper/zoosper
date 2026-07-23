<?php

declare(strict_types=1);

/**
 * Guarded patch tool for adding a disabled-by-default report-only admin
 * rate-limit hook to app/zoosper-auth/config/admin_middleware.php.
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
$alreadyPresent = false;
$canPatch = false;
$patched = false;

if (! is_file($targetPath)) {
    $errors[] = 'Target admin middleware config missing: ' . $targetRelative;
    $source = '';
} else {
    $source = (string) file_get_contents($targetPath);
}

$alreadyPresent = str_contains($source, 'RateLimitMiddlewareIntegration')
    || str_contains($source, 'Phase 1.39 report-only rate-limit hook');

if ($alreadyPresent) {
    $actions[] = 'Admin middleware rate-limit hook already appears to be present.';
} elseif (! str_contains($source, 'return [')) {
    $errors[] = 'Target does not look like a simple return array config.';
} elseif (! str_contains($source, 'AuthenticationMiddleware')) {
    $errors[] = 'Target does not mention AuthenticationMiddleware; refusing to patch unexpected shape.';
} elseif (! str_contains($source, 'Csrf') && stripos($source, 'csrf') === false) {
    $errors[] = 'Target does not mention CSRF; refusing to patch unexpected shape.';
} else {
    $canPatch = true;
    $actions[] = 'Target has expected return-array/authentication/CSRF shape.';
}

$hook = <<<'PHP'
    // Phase 1.39 report-only rate-limit hook. Disabled by default via app/zoosper-core/config/rate_limit.php.
    static function ($request, callable $next) {
        $root = dirname(__DIR__, 3);
        $configPath = $root . '/app/zoosper-core/config/rate_limit.php';
        $config = is_file($configPath) ? require $configPath : [];

        if (! is_array($config) || ($config['enabled'] ?? false) !== true || ($config['mode'] ?? 'report_only') !== 'report_only') {
            return $next($request);
        }

        // Report-only runtime integration is intentionally deferred to the dedicated middleware adapter.
        // This hook proves the admin middleware stack can carry the disabled-by-default seam safely.
        return $next($request);
    },
PHP;

if ($apply && $errors === [] && $canPatch && ! $alreadyPresent) {
    $backupPath = $targetPath . '.phase-1.39-rate-limit-hook.bak';
    copy($targetPath, $backupPath);

    $newSource = preg_replace('/return\s*\[\s*\n/', 'return [' . PHP_EOL . $hook . PHP_EOL, $source, 1);
    if (! is_string($newSource) || $newSource === $source) {
        $errors[] = 'Patch failed: source unchanged.';
    } else {
        file_put_contents($targetPath, $newSource);
        $patched = true;
        $alreadyPresent = true;
        $actions[] = 'Patched disabled-by-default report-only rate-limit hook into ' . $targetRelative;
        $actions[] = 'Backup written to ' . basename($backupPath);
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-middleware-hook-apply.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-middleware-hook-apply.log';

$report = [];
$report[] = '# Rate Limit Admin Middleware Hook Apply Report';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$report[] = 'Target: ' . $targetRelative;
$report[] = 'Hook present: ' . ($alreadyPresent ? 'yes' : 'no');
$report[] = 'Can patch: ' . ($canPatch ? 'yes' : 'no');
$report[] = 'Patched: ' . ($patched ? 'yes' : 'no');
$report[] = 'Errors: ' . count($errors);

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
$log[] = 'Rate limit admin middleware hook apply report written to: ' . $reportPath;
$log[] = 'MODE ' . ($apply ? 'apply' : 'dry-run');
$log[] = 'RATE_LIMIT_ADMIN_MIDDLEWARE_HOOK_PRESENT ' . ($alreadyPresent ? 'yes' : 'no');
$log[] = 'CAN_PATCH_RATE_LIMIT_ADMIN_MIDDLEWARE_HOOK ' . ($canPatch ? 'yes' : 'no');
$log[] = 'RATE_LIMIT_ADMIN_MIDDLEWARE_HOOK_PATCHED ' . ($patched ? 'yes' : 'no');
$log[] = 'RATE_LIMIT_ADMIN_MIDDLEWARE_HOOK_APPLY_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
