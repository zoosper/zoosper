<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$targets = [
    'app/zoosper-admin/src/Form/AdminFormConfigAggregator.php',
    'app/zoosper-admin/src/Form/AdminFormUiConfigLoader.php',
];

$errors = 0;
$report = [];
$report[] = '## Admin Form Config Runtime Migration Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($targets as $relativePath) {
    $path = $root . '/' . $relativePath;
    $exists = is_file($path);
    $source = $exists ? (string) file_get_contents($path) : '';

    $report[] = '### ' . $relativePath;
    $report[] = '- exists: ' . ($exists ? 'yes' : 'no');
    $report[] = '- has ConfigFileLayeredLoader reference: ' . (str_contains($source, 'ConfigFileLayeredLoader') ? 'yes' : 'no');
    $report[] = '- has phase marker: ' . (str_contains($source, 'PHASE_140DF_') ? 'yes' : 'no');
    $report[] = '- has backup: ' . (is_file($path . '.phase140df.bak') ? 'yes' : 'no');
    $report[] = '';

    if (!$exists) {
        $errors++;
    }
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/admin-form-config-runtime-migration.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/admin-form-config-runtime-migration.log', "ADMIN_FORM_CONFIG_RUNTIME_MIGRATION_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
