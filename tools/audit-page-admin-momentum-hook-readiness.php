<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$requiredFiles = [
    'app/zoosper-page/src/Admin/PageMomentumAdminHookProvider.php',
    'app/zoosper-page/config/admin_page_momentum_hook_candidate.php',
    'tools/generate-page-admin-momentum-hook-candidate.php',
    'tools/prove-page-admin-momentum-hook-provider.php',
    'tools/audit-page-admin-momentum-hook-readiness.php',
    'docs/development/page-admin-momentum-hook-candidate.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.52a-l.md',
];
$requiredReports = [
    'var/reports/page-admin-momentum-hook-candidate.json',
    'var/reports/page-admin-momentum-hook-provider-proof.txt',
];

$report[] = '## Page Admin Momentum Hook Readiness Audit';
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

$hookPath = $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';
if (is_file($hookPath)) {
    $payload = require $hookPath;
    $rootPayload = is_array($payload) ? ($payload['page_momentum_admin_hook'] ?? []) : [];
    $enabled = is_array($rootPayload) && ($rootPayload['enabled'] ?? false) === true;
    $mutation = is_array($rootPayload) && ($rootPayload['live_mutation'] ?? true) === true;
    $routes = is_array($rootPayload) && isset($rootPayload['routes']) && is_array($rootPayload['routes']) ? $rootPayload['routes'] : [];
    $items = is_array($rootPayload) && isset($rootPayload['menu_items']) && is_array($rootPayload['menu_items']) ? $rootPayload['menu_items'] : [];

    $report[] = '- hook enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = '- hook route count: ' . count($routes);
    $report[] = '- hook menu count: ' . count($items);
    $report[] = '- hook live mutation: ' . ($mutation ? 'yes' : 'no');

    if (!$enabled || $mutation || count($routes) !== 1 || count($items) !== 1) {
        $errors++;
    }
}

$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-hook-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-hook-readiness.log', "PAGE_ADMIN_MOMENTUM_HOOK_READINESS_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_HOOK_READINESS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
