<?php

declare(strict_types=1);

/** Read-only readiness audit for admin form config layering migration. */

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
    'docs/development/admin-form-config-layering-migration-plan.md',
    'tools/discover-admin-form-config-loader.php',
    'tools/plan-admin-form-config-layered-loader.php',
    'tools/audit-admin-form-config-layering-readiness.php',
    'app/zoosper-core/src/Config/ConfigFileLayeredLoader.php',
    'app/zoosper-core/tests/Unit/Config/AdminFormConfigLayeringReadinessTest.php',
];
$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required admin form config layering file missing: ' . $relative;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin-form-config-layering-readiness.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin-form-config-layering-readiness.log';
$report = [];
$report[] = '# Admin Form Config Layering Readiness';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Runtime migration applied: no';
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
$log[] = 'Admin form config layering readiness written to: ' . $reportPath;
$log[] = 'ADMIN_FORM_CONFIG_LAYERING_READINESS_ERRORS ' . count($errors);
$log[] = 'ADMIN_FORM_CONFIG_RUNTIME_MIGRATION_APPLIED no';
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);
echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
