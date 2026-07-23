<?php

declare(strict_types=1);

/**
 * Discover admin form/UI config loader source files for layered-config migration.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

$sourceDir = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin-form-config-loader-source';
if (! is_dir($sourceDir) && ! mkdir($sourceDir, 0775, true) && ! is_dir($sourceDir)) {
    fwrite(STDERR, 'Unable to create source output directory: ' . $sourceDir . PHP_EOL);
    exit(1);
}

$candidates = [
    'app/zoosper-admin/src/Form/AdminFormUiConfigLoader.php',
    'app/zoosper-admin/src/Form/AdminFormConfigAggregator.php',
    'app/zoosper-core/src/Config/ConfigFileLayeredLoader.php',
    'app/zoosper-core/src/Config/LayeredConfigLoader.php',
    'app/zoosper-page/config/admin_forms.php',
    'app/zoosper-page/config/admin_ui.php',
    'app/zoosper-auth/config/admin_ui.php',
];

$errors = [];
$signals = [];
foreach ($candidates as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $exists = is_file($path);
    $source = $exists ? (string) file_get_contents($path) : '';
    if ($exists) {
        $safeName = str_replace(['/', '\\'], '__', $relative) . '.txt';
        file_put_contents($sourceDir . DIRECTORY_SEPARATOR . $safeName, $source);
    }

    $signals[$relative] = [
        'exists' => $exists,
        'contains require' => str_contains($source, 'require'),
        'contains config path' => str_contains($source, 'config/'),
        'contains admin_forms' => str_contains($source, 'admin_forms'),
        'contains admin_ui' => str_contains($source, 'admin_ui'),
        'contains LayeredConfigLoader' => str_contains($source, 'LayeredConfigLoader'),
        'contains ConfigFileLayeredLoader' => str_contains($source, 'ConfigFileLayeredLoader'),
        'contains return array' => preg_match('/return\s*\[/', $source) === 1,
    ];
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin-form-config-loader-discovery.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin-form-config-loader-discovery.log';

$report = [];
$report[] = '# Admin Form Config Loader Discovery';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Source output directory: ' . $sourceDir;
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Candidate signals';
foreach ($signals as $relative => $items) {
    $report[] = '';
    $report[] = '### ' . $relative;
    foreach ($items as $name => $value) {
        $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
    }
}
file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Admin form config loader discovery written to: ' . $reportPath;
$log[] = 'ADMIN_FORM_CONFIG_LOADER_DISCOVERY_ERRORS ' . count($errors);
$log[] = 'SOURCE_DIR ' . $sourceDir;
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit(0);
