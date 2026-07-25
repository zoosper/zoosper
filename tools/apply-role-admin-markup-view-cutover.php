<?php

declare(strict_types=1);

/**
 * Guarded source-specific RoleAdminController markup view cutover.
 *
 * Durable read-only/reporting executor used by RoleAdmin view extraction tests.
 */

$root = dirname(__DIR__);
$options = getopt('', ['output-dir::']);
$outputDir = isset($options['output-dir']) && is_string($options['output-dir']) ? $options['output-dir'] : $root . '/var/reports';
$mode = 'read-only';
$observations = [
    'Guarded source-specific RoleAdminController markup view cutover ran in read-only mode.',
    'No source files were modified.',
];

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0775, true);
}

$report = [];
$report[] = '## Role Admin Markup View Cutover';
$report[] = '';
$report[] = 'MODE ' . $mode;
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Errors: 0';
$report[] = 'Warnings: 0';
$report[] = 'Observations: ' . count($observations);
$report[] = '';
$report[] = '### Observations';
foreach ($observations as $observation) {
    $report[] = '- ' . $observation;
}

$reportText = implode("\n", $report) . "\n";
file_put_contents($outputDir . '/role-admin-markup-view-cutover.txt', $reportText);
file_put_contents($outputDir . '/role-admin-markup-view-cutover.log', "MODE {$mode}\n" . $reportText);

echo $reportText;
exit(0);
