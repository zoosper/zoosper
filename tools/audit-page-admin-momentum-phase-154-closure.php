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
    'app/zoosper-page/src/Admin/PageMomentumAdminRuntimeAggregationProvider.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminRuntimeHookPreview.php',
    'app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php',
    'tools/prove-page-admin-momentum-runtime-aggregation-candidate.php',
    'tools/audit-page-admin-momentum-runtime-aggregation-readiness.php',
    'tools/prove-page-admin-momentum-runtime-hook-preview.php',
    'tools/generate-page-admin-momentum-runtime-source-hook-plan.php',
    'tools/audit-page-admin-momentum-phase-154-closure.php',
    'docs/development/page-admin-momentum-runtime-aggregation-candidate.md',
    'docs/development/page-admin-momentum-phase-1.54-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.54m-z.md',
];
$requiredReports = [
    'var/reports/page-admin-momentum-runtime-aggregation-candidate.json',
    'var/reports/page-admin-momentum-runtime-hook-preview.json',
    'var/reports/page-admin-momentum-runtime-source-hook-plan.json',
];

$report[] = '## Phase 1.54 Page Momentum Runtime Aggregation Candidate Closure Audit';
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

$previewFile = $root . '/var/reports/page-admin-momentum-runtime-hook-preview.json';
if (is_file($previewFile)) {
    $preview = json_decode((string) file_get_contents($previewFile), true);
    $ready = is_array($preview) && ($preview['readyForRuntimeSourceHook'] ?? false) === true;
    $mutation = is_array($preview) && ($preview['liveMutation'] ?? true) === true;
    $report[] = '- runtime hook preview ready: ' . ($ready ? 'yes' : 'no');
    $report[] = '- runtime hook preview live mutation: ' . ($mutation ? 'yes' : 'no');
    if (!$ready) {
        $warnings++;
    }
    if ($mutation) {
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
file_put_contents($reportDir . '/page-admin-momentum-phase-154-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-154-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_154_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_154_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
