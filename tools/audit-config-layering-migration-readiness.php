<?php

declare(strict_types=1);

/**
 * Read-only readiness audit for first layered config migration.
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
    'docs/development/config-layering-source-discovery.md',
    'app/zoosper-core/src/Config/LayeredConfigLoader.php',
    'app/zoosper-core/src/Config/LayeredConfigResult.php',
    'tools/audit-config-sources.php',
    'tools/plan-config-layering-first-migration.php',
    'tools/audit-config-layering-migration-readiness.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required migration-readiness file missing: ' . $relative;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-layering-migration-readiness.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-layering-migration-readiness.log';

$report = [];
$report[] = '# Config Layering Migration Readiness';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Status: discovery-only; no runtime config migration has been applied.';
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
$log[] = 'Config layering migration readiness written to: ' . $reportPath;
$log[] = 'CONFIG_LAYERING_MIGRATION_READINESS_ERRORS ' . count($errors);
$log[] = 'CONFIG_LAYERING_RUNTIME_MIGRATION_APPLIED no';
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
