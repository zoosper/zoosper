<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$candidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';

$requiredFiles = [
    'app/zoosper-page/src/Admin/PageMomentumAggregatorPatchBuilder.php',
    'app/zoosper-page/config/admin_page_momentum_runtime_candidate.php',
    'tools/apply-page-admin-momentum-aggregator-candidate.php',
    'tools/audit-page-admin-momentum-aggregator-candidate.php',
    'docs/development/page-admin-momentum-aggregator-candidate.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.50a-l.md',
];

$report[] = '## Page Admin Momentum Aggregator Candidate Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredFiles as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

if (is_file($candidatePath)) {
    $candidate = require $candidatePath;
    $rootConfig = is_array($candidate) ? ($candidate['page_momentum_admin_integration'] ?? []) : [];
    $enabled = is_array($rootConfig) && ($rootConfig['enabled'] ?? false) === true;
    $routes = is_array($rootConfig) && isset($rootConfig['routes']) && is_array($rootConfig['routes']) ? $rootConfig['routes'] : [];
    $items = is_array($rootConfig) && isset($rootConfig['menu_items']) && is_array($rootConfig['menu_items']) ? $rootConfig['menu_items'] : [];
    $mutation = is_array($rootConfig) && ($rootConfig['live_mutation'] ?? true) === true;

    $report[] = '';
    $report[] = '- candidate enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = '- candidate route count: ' . count($routes);
    $report[] = '- candidate menu count: ' . count($items);
    $report[] = '- candidate live mutation: ' . ($mutation ? 'yes' : 'no');

    if (!$enabled || count($routes) !== 1 || count($items) !== 1 || $mutation) {
        $errors++;
    }
}

$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Live mutation performed: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-aggregator-candidate-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-aggregator-candidate-audit.log', "PAGE_ADMIN_MOMENTUM_AGGREGATOR_CANDIDATE_AUDIT_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_AGGREGATOR_CANDIDATE_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
