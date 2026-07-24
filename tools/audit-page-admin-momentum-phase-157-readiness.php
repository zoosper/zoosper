<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageMomentumStatusProvider.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'tools/smoke-page-admin-momentum-live-panel.php',
    'tools/audit-page-admin-momentum-phase-157-readiness.php',
    'docs/development/page-admin-momentum-live-panel.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.57a-l.md',
];
$requiredReports = [
    'var/reports/page-admin-momentum-live-panel-smoke.txt',
];

$report[] = '## Phase 1.57 Page Momentum Live Panel Readiness Audit';
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

$report[] = 'Panel route expected: /admin/page-momentum';
$report[] = 'Panel permission expected: page.manage';
$report[] = 'Panel mode expected: read-only';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-157-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-157-readiness.log', "PAGE_ADMIN_MOMENTUM_PHASE_157_READINESS_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_157_READINESS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
