<?php

declare(strict_types=1);

/**
 * Read-only audit for Phase 1.40 config layering foundation.
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
    'docs/development/config-layering-foundation.md',
    'docs/architecture/adr-config-layering.md',
    'app/zoosper-core/src/Config/LayeredConfigResult.php',
    'app/zoosper-core/src/Config/LayeredConfigLoader.php',
    'app/zoosper-core/tests/Unit/Config/LayeredConfigLoaderTest.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required config-layering file missing: ' . $relative;
    }
}

$loaderPath = $root . DIRECTORY_SEPARATOR . 'app/zoosper-core/src/Config/LayeredConfigLoader.php';
$loaderSource = is_file($loaderPath) ? (string) file_get_contents($loaderPath) : '';
foreach (['LayeredConfigResult', 'merge', 'isAssociative'] as $needle) {
    if (! str_contains($loaderSource, $needle)) {
        $errors[] = 'LayeredConfigLoader missing expected token: ' . $needle;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-layering-foundation.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-layering-foundation.log';

$report = [];
$report[] = '# Config Layering Foundation Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
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
$log[] = 'Config layering foundation audit written to: ' . $reportPath;
$log[] = 'CONFIG_LAYERING_FOUNDATION_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
