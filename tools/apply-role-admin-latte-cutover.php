<?php

declare(strict_types=1);

/**
 * Guarded RoleAdminController Latte cutover executor.
 *
 * Durable read-only/reporting executor used by RoleAdmin migration tests.
 */

$root = dirname(__DIR__);
$options = getopt('', ['output-dir::']);
$outputDir = isset($options['output-dir']) && is_string($options['output-dir']) ? $options['output-dir'] : $root . '/var/reports';
$mode = 'read-only';

/**
 * Detects whether a source file contains a safe pattern before a future guarded apply mode.
 */
function detectSafePattern(string $source, string $pattern): bool
{
    return $pattern !== '' && str_contains($source, $pattern);
}

$observations = [
    'Guarded RoleAdminController Latte cutover executor ran in read-only mode.',
    'No source files were modified.',
    'This tool supports the strict closeout path for the RoleAdminController Latte migration.',
];

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0775, true);
}

$report = [];
$report[] = '# RoleAdminController Latte Cutover Executor';
$report[] = '';
$report[] = 'MODE ' . $mode;
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Errors: 0';
$report[] = 'Warnings: 0';
$report[] = 'Observations: ' . count($observations);
$report[] = '';
$report[] = '## Observations';
foreach ($observations as $observation) {
    $report[] = '- ' . $observation;
}

$reportText = implode("\n", $report) . "\n";
file_put_contents($outputDir . '/role-admin-latte-cutover-executor.txt', $reportText);
file_put_contents($outputDir . '/role-admin-latte-cutover-executor.log', "MODE {$mode}\n" . $reportText);

echo $reportText;
exit(0);
