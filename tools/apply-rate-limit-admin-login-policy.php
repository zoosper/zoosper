<?php

declare(strict_types=1);

/**
 * Guarded patch tool for adding the real admin.login rate-limit policy while
 * keeping rate limiting disabled by default.
 *
 * Important: this version only treats admin.login as present when it exists in
 * the parsed returned config array. Commented examples do not count.
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

$configRelative = 'app/zoosper-core/config/rate_limit.php';
$configPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $configRelative);
$errors = [];
$actions = [];
$alreadyPresent = false;
$canPatch = false;
$patched = false;

if (! is_file($configPath)) {
    $errors[] = 'Rate limit config missing: ' . $configRelative;
    $source = '';
    $config = [];
} else {
    $source = (string) file_get_contents($configPath);
    $config = require $configPath;
    if (! is_array($config)) {
        $errors[] = 'Rate limit config did not return an array.';
        $config = [];
    }
}

if (($config['enabled'] ?? null) !== false) {
    $errors[] = 'Refusing admin.login policy patch because rate_limit.php is not disabled by default.';
}

if (($config['mode'] ?? null) !== 'report_only') {
    $errors[] = 'Refusing admin.login policy patch because rate_limit.php does not default to report_only.';
}

$alreadyPresent = isset(($config['policies'] ?? [])['admin.login']);
if ($alreadyPresent) {
    $actions[] = 'admin.login policy already exists in parsed config.';
} elseif (preg_match("/'policies'\s*=>\s*\[/", $source) === 1) {
    $canPatch = true;
    $actions[] = 'admin.login policy can be inserted into policies array.';
} else {
    $errors[] = 'Could not find a simple policies array to patch.';
}

if ($apply && $errors === [] && $canPatch && ! $alreadyPresent) {
    $backupPath = $configPath . '.phase-1.39-admin-login-policy.bak';
    copy($configPath, $backupPath);

    $policy = "        'admin.login' => [\n"
        . "            'scope' => 'admin',\n"
        . "            'max_attempts' => 5,\n"
        . "            'window_seconds' => 300,\n"
        . "        ],\n";

    $newSource = preg_replace("/('policies'\s*=>\s*\[\s*\n)/", '$1' . $policy, $source, 1);
    if (! is_string($newSource) || $newSource === $source) {
        $errors[] = 'Patch failed: source was unchanged.';
    } else {
        file_put_contents($configPath, $newSource);
        $patched = true;
        $actions[] = 'Patched real admin.login policy into ' . $configRelative;
        $actions[] = 'Backup written to ' . basename($backupPath);
    }
}

if ($apply && $errors === [] && ($patched || $alreadyPresent)) {
    $after = require $configPath;
    if (! is_array($after) || ! isset(($after['policies'] ?? [])['admin.login'])) {
        $errors[] = 'Post-patch verification failed: parsed admin.login policy is still missing.';
    } else {
        $alreadyPresent = true;
        $actions[] = 'Post-patch verification confirmed parsed admin.login policy exists.';
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-policy-apply.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-policy-apply.log';

$report = [];
$report[] = '# Admin Login Rate Limit Policy Apply Report';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$report[] = 'Config: ' . $configRelative;
$report[] = 'Parsed admin.login present: ' . ($alreadyPresent ? 'yes' : 'no');
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
$log[] = 'Admin login rate-limit policy apply report written to: ' . $reportPath;
$log[] = 'MODE ' . ($apply ? 'apply' : 'dry-run');
$log[] = 'ADMIN_LOGIN_POLICY_PRESENT ' . ($alreadyPresent ? 'yes' : 'no');
$log[] = 'CAN_PATCH_ADMIN_LOGIN_POLICY ' . ($canPatch ? 'yes' : 'no');
$log[] = 'ADMIN_LOGIN_POLICY_PATCHED ' . ($patched ? 'yes' : 'no');
$log[] = 'ADMIN_LOGIN_POLICY_APPLY_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
