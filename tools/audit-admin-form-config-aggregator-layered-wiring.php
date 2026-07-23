<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$target = $root . '/app/zoosper-admin/src/Form/AdminFormConfigAggregator.php';
$relativeTarget = 'app/zoosper-admin/src/Form/AdminFormConfigAggregator.php';
$errors = 0;
$report = [];

$report[] = '## AdminFormConfigAggregator Layered Wiring Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Target: ' . $relativeTarget;
$exists = is_file($target);
$report[] = '- exists: ' . ($exists ? 'yes' : 'no');

$source = $exists ? (string) file_get_contents($target) : '';
$hasBridge = str_contains($source, 'AdminConfigLayeredFileLoader');
$hasMarker = str_contains($source, 'PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED');
$hasHelper = str_contains($source, 'loadLayeredAdminFormConfigFile');
$hasBackup = is_file($target . '.phase140qr.bak');
$remainingRequireAssignments = preg_match_all('/\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*require\b/', $source, $matches);

$report[] = '- has AdminConfigLayeredFileLoader reference: ' . ($hasBridge ? 'yes' : 'no');
$report[] = '- has phase marker: ' . ($hasMarker ? 'yes' : 'no');
$report[] = '- has helper: ' . ($hasHelper ? 'yes' : 'no');
$report[] = '- has backup: ' . ($hasBackup ? 'yes' : 'no');
$report[] = '- remaining require assignments: ' . (string) $remainingRequireAssignments;

if (!$exists || !$hasBridge || !$hasMarker || !$hasHelper || $remainingRequireAssignments > 0) {
    $errors++;
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/admin-form-config-aggregator-layered-wiring.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/admin-form-config-aggregator-layered-wiring.log', "ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED_WIRING_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
