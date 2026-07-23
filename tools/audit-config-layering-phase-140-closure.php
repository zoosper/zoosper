<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$errors = 0;
$warnings = 0;
$report = [];

$report[] = '## Phase 1.40 Config Layering Closure Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$requiredClasses = [
    'Zoosper\\Core\\Config\\ConfigLayerSource',
    'Zoosper\\Core\\Config\\ConfigFileLayeredLoader',
    'Zoosper\\Core\\Config\\LayeredConfigLoader',
    'Zoosper\\Core\\Config\\LayeredConfigResult',
    'Zoosper\\Admin\\Form\\AdminConfigLayeredFileLoader',
    'Zoosper\\Admin\\Form\\AdminFormConfigAggregator',
];

$report[] = '### Required classes';
foreach ($requiredClasses as $class) {
    $exists = class_exists($class);
    $report[] = '- ' . $class . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$requiredFiles = [
    'app/zoosper-admin/src/Form/AdminConfigLayeredFileLoader.php',
    'app/zoosper-admin/src/Form/AdminFormConfigAggregator.php',
    'tools/prove-admin-config-layered-runtime-bridge.php',
    'tools/apply-admin-form-config-aggregator-layered-loader.php',
    'tools/audit-admin-form-config-aggregator-layered-wiring.php',
    'docs/development/admin-config-layered-runtime-bridge.md',
    'docs/development/admin-form-config-aggregator-layered-wiring.md',
];

$report[] = '';
$report[] = '### Required files';
foreach ($requiredFiles as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$aggregatorPath = $root . '/app/zoosper-admin/src/Form/AdminFormConfigAggregator.php';
$aggregatorSource = is_file($aggregatorPath) ? (string) file_get_contents($aggregatorPath) : '';
$remainingRequireAssignments = preg_match_all('/\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*require\b/', $aggregatorSource, $matches);
$hasBridge = str_contains($aggregatorSource, 'AdminConfigLayeredFileLoader');
$hasMarker = str_contains($aggregatorSource, 'PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED');
$hasHelper = str_contains($aggregatorSource, 'loadLayeredAdminFormConfigFile');

$report[] = '';
$report[] = '### AdminFormConfigAggregator drift guard';
$report[] = '- has AdminConfigLayeredFileLoader reference: ' . ($hasBridge ? 'yes' : 'no');
$report[] = '- has phase marker: ' . ($hasMarker ? 'yes' : 'no');
$report[] = '- has layered helper: ' . ($hasHelper ? 'yes' : 'no');
$report[] = '- remaining require assignments: ' . (string) $remainingRequireAssignments;

if (!$hasBridge || !$hasMarker || !$hasHelper || $remainingRequireAssignments > 0) {
    $errors++;
}

$backupFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    $path = $file->getPathname();
    if (preg_match('/\.phase140[a-z0-9-]*\.bak$/', $path)) {
        $backupFiles[] = str_replace($root . '/', '', $path);
    }
}

$report[] = '';
$report[] = '### Backup artefact hygiene';
$report[] = '- phase 1.40 backup files: ' . count($backupFiles);
foreach ($backupFiles as $backupFile) {
    $report[] = '  - ' . $backupFile;
}
if ($backupFiles !== []) {
    $warnings++;
}

$report[] = '';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/config-layering-phase-140-closure.txt', implode("\n", $report) . "\n");
file_put_contents(
    $reportDir . '/config-layering-phase-140-closure.log',
    "CONFIG_LAYERING_PHASE_140_CLOSURE_WARNINGS {$warnings}\n" .
    "CONFIG_LAYERING_PHASE_140_CLOSURE_ERRORS {$errors}\n"
);

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
