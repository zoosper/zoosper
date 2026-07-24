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
    'app/zoosper-page/src/Admin/PageMomentumAggregatorPatchDraft.php',
    'tools/discover-admin-route-menu-aggregators.php',
    'tools/generate-page-admin-momentum-aggregator-integration-plan.php',
    'tools/generate-page-admin-momentum-aggregator-patch-draft.php',
    'tools/audit-page-admin-momentum-aggregator-readiness.php',
    'tools/audit-page-admin-momentum-phase-149-closure.php',
    'docs/development/page-admin-momentum-aggregator-readiness.md',
    'docs/development/page-admin-momentum-phase-1.49-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.49m-z.md',
];
$requiredReports = [
    'var/reports/admin-route-menu-aggregator-discovery.json',
    'var/reports/page-admin-momentum-aggregator-integration-plan.json',
    'var/reports/page-admin-momentum-aggregator-patch-draft.json',
];

$report[] = '## Phase 1.49 Page Admin Momentum Aggregation Closure Audit';
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

$draftFile = $root . '/var/reports/page-admin-momentum-aggregator-patch-draft.json';
if (is_file($draftFile)) {
    $draft = json_decode((string) file_get_contents($draftFile), true);
    $ready = is_array($draft) && ($draft['readyForPatchDraft'] ?? false) === true;
    $mutation = is_array($draft) && ($draft['liveMutation'] ?? true) === true;
    $report[] = '- patch draft ready: ' . ($ready ? 'yes' : 'no');
    $report[] = '- patch draft live mutation: ' . ($mutation ? 'yes' : 'no');
    if (!$ready) {
        $warnings++;
    }
    if ($mutation) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Live mutation performed: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-149-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-149-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_149_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_149_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
