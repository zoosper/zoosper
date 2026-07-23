<?php

declare(strict_types=1);

/**
 * Source-specific planner for migrating admin form/UI config loading to ConfigFileLayeredLoader.
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

$targets = [
    'app/zoosper-admin/src/Form/AdminFormUiConfigLoader.php',
    'app/zoosper-admin/src/Form/AdminFormConfigAggregator.php',
];

$errors = [];
$plans = [];
foreach ($targets as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (! is_file($path)) {
        $plans[$relative] = ['exists' => false, 'can_patch_later' => false, 'reasons' => ['target missing']];
        continue;
    }

    $source = (string) file_get_contents($path);
    $reasons = [];
    $canPatchLater = true;

    if (! str_contains($source, 'require')) {
        $canPatchLater = false;
        $reasons[] = 'does not use require-style config loading signal';
    } else {
        $reasons[] = 'uses require-style config loading signal';
    }

    if (! str_contains($source, 'admin_forms') && ! str_contains($source, 'admin_ui')) {
        $reasons[] = 'does not explicitly mention admin_forms/admin_ui';
    } else {
        $reasons[] = 'mentions admin_forms/admin_ui';
    }

    if (str_contains($source, 'ConfigFileLayeredLoader')) {
        $reasons[] = 'already mentions ConfigFileLayeredLoader';
    }

    $plans[$relative] = [
        'exists' => true,
        'can_patch_later' => $canPatchLater,
        'reasons' => $reasons,
    ];
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin-form-config-layered-loader-plan.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin-form-config-layered-loader-plan.log';
$canPatchAny = false;

$report = [];
$report[] = '# Admin Form Config Layered Loader Plan';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
foreach ($plans as $relative => $plan) {
    $canPatchAny = $canPatchAny || (bool) $plan['can_patch_later'];
    $report[] = '';
    $report[] = '## ' . $relative;
    $report[] = '- exists: ' . ($plan['exists'] ? 'yes' : 'no');
    $report[] = '- can patch later: ' . ($plan['can_patch_later'] ? 'yes' : 'no');
    foreach ($plan['reasons'] as $reason) {
        $report[] = '- ' . $reason;
    }
}
$report[] = '';
$report[] = '## Recommendation';
$report[] = $canPatchAny
    ? 'A future guarded patch can migrate the safest matching admin form/UI loader to ConfigFileLayeredLoader.'
    : 'Do not patch yet; inspect source snapshots first.';
file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Admin form config layered loader plan written to: ' . $reportPath;
$log[] = 'ADMIN_FORM_CONFIG_LAYERED_LOADER_PLAN_ERRORS ' . count($errors);
$log[] = 'ADMIN_FORM_CONFIG_CAN_PATCH_LATER ' . ($canPatchAny ? 'yes' : 'no');
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
