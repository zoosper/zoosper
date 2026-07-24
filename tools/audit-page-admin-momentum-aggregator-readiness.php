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
    'app/zoosper-page/src/Admin/PageMomentumAggregatorIntegrationPlan.php',
    'tools/discover-admin-route-menu-aggregators.php',
    'tools/generate-page-admin-momentum-aggregator-integration-plan.php',
    'tools/audit-page-admin-momentum-aggregator-readiness.php',
    'docs/development/page-admin-momentum-aggregator-readiness.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.49a-l.md',
];
$requiredReports = [
    'var/reports/admin-route-menu-aggregator-discovery.json',
    'var/reports/page-admin-momentum-aggregator-integration-plan.json',
];

$report[] = '## Page Admin Momentum Aggregator Readiness Audit';
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

$planFile = $root . '/var/reports/page-admin-momentum-aggregator-integration-plan.json';
if (is_file($planFile)) {
    $plan = json_decode((string) file_get_contents($planFile), true);
    $ready = is_array($plan) && ($plan['readyForPatchDraft'] ?? false) === true;
    $report[] = '- ready for patch draft: ' . ($ready ? 'yes' : 'no');
    if (!$ready) {
        $warnings++;
    }
}

$report[] = 'Live mutation performed: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-aggregator-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-aggregator-readiness.log', "PAGE_ADMIN_MOMENTUM_AGGREGATOR_READINESS_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_AGGREGATOR_READINESS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
