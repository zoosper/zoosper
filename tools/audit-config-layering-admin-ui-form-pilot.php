<?php

declare(strict_types=1);

/** Read-only audit for admin UI/form config layering pilot. */

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
    'docs/development/config-layering-admin-ui-form-pilot.md',
    'app/zoosper-core/src/Config/LayeredConfigLoader.php',
    'app/zoosper-core/src/Config/ConfigLayerSource.php',
    'app/zoosper-core/src/Config/ConfigFileLayeredLoader.php',
    'tools/smoke-admin-ui-form-config-layering.php',
    'tools/audit-config-layering-admin-ui-form-pilot.php',
    'app/zoosper-core/tests/Unit/Config/AdminUiFormConfigLayeringPilotTest.php',
];
$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required admin UI/form layering file missing: ' . $relative;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-layering-admin-ui-form-pilot.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-layering-admin-ui-form-pilot.log';
$report = [];
$report[] = '# Admin UI/Form Config Layering Pilot Audit';
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
$log[] = 'Admin UI/form config layering pilot audit written to: ' . $reportPath;
$log[] = 'CONFIG_LAYERING_ADMIN_UI_FORM_PILOT_ERRORS ' . count($errors);
$log[] = 'CONFIG_LAYERING_ADMIN_UI_FORM_RUNTIME_MIGRATION_APPLIED no';
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);
echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
