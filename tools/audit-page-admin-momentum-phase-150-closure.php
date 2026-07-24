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
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageMomentumAggregatorPatchBuilder.php',
    'app/zoosper-page/src/Admin/PageMomentumAggregatorCandidateConsumer.php',
    'app/zoosper-page/config/admin_page_momentum_runtime_candidate.php',
    'tools/apply-page-admin-momentum-aggregator-candidate.php',
    'tools/audit-page-admin-momentum-aggregator-candidate.php',
    'tools/prove-page-admin-momentum-candidate-consumer.php',
    'tools/audit-page-admin-momentum-phase-150-closure.php',
    'docs/development/page-admin-momentum-aggregator-candidate.md',
    'docs/development/page-admin-momentum-phase-1.50-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.50m-z.md',
];
$requiredReports = [
    'var/reports/page-admin-momentum-aggregator-candidate.json',
    'var/reports/page-admin-momentum-aggregator-candidate-audit.txt',
    'var/reports/page-admin-momentum-candidate-consumer-proof.txt',
];

$report[] = '## Phase 1.50 Page Admin Momentum Aggregator Candidate Closure Audit';
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
foreach ($requiredReports as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$candidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';
if (is_file($candidatePath)) {
    $candidate = require $candidatePath;
    $integration = is_array($candidate) ? ($candidate['page_momentum_admin_integration'] ?? []) : [];
    $enabled = is_array($integration) && ($integration['enabled'] ?? false) === true;
    $mutation = is_array($integration) && ($integration['live_mutation'] ?? true) === true;
    $routes = is_array($integration) && isset($integration['routes']) && is_array($integration['routes']) ? $integration['routes'] : [];
    $items = is_array($integration) && isset($integration['menu_items']) && is_array($integration['menu_items']) ? $integration['menu_items'] : [];

    $report[] = '';
    $report[] = '- candidate enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = '- candidate route count: ' . count($routes);
    $report[] = '- candidate menu count: ' . count($items);
    $report[] = '- candidate live mutation: ' . ($mutation ? 'yes' : 'no');

    if (!$enabled || count($routes) !== 1 || count($items) !== 1 || $mutation) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Live mutation performed: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-150-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-150-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_150_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_150_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
