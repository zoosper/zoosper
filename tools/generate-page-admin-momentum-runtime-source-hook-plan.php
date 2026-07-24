<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$previewFile = $root . '/var/reports/page-admin-momentum-runtime-hook-preview.json';
$discoveryFile = $root . '/var/reports/admin-route-menu-aggregator-discovery.json';

$report[] = '## Page Momentum Runtime Source Hook Plan';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($previewFile)) {
    $report[] = 'Runtime hook preview JSON missing. Run tools/prove-page-admin-momentum-runtime-hook-preview.php first.';
    $errors++;
} else {
    $preview = json_decode((string) file_get_contents($previewFile), true);
    $ready = is_array($preview) && ($preview['readyForRuntimeSourceHook'] ?? false) === true;
    $discovery = is_file($discoveryFile) ? json_decode((string) file_get_contents($discoveryFile), true) : [];
    $routeFiles = is_array($discovery) && isset($discovery['routeFiles']) && is_array($discovery['routeFiles']) ? $discovery['routeFiles'] : [];
    $menuFiles = is_array($discovery) && isset($discovery['menuFiles']) && is_array($discovery['menuFiles']) ? $discovery['menuFiles'] : [];

    $plan = [
        'readyForRuntimeSourceHook' => $ready,
        'liveMutation' => false,
        'routeFilesDiscovered' => $routeFiles,
        'menuFilesDiscovered' => $menuFiles,
        'recommendedPatch' => [
            'inspect the discovered admin route/menu aggregation files before editing',
            'load PageMomentumAdminRuntimeAggregationProvider from the page module',
            'provide payload from admin_page_momentum_runtime_aggregation_candidate.php and admin_page_momentum_hook_candidate.php',
            'append exactly one admin.page_momentum.index route if not already present',
            'append exactly one Page momentum menu item if not already present',
            'guard route and menu with page.manage',
            'smoke /admin and /admin/page-momentum, run Pest, and inspect logs',
        ],
        'rollback' => [
            'remove the runtime aggregation hook added in the next phase',
            'keep the isolated page-module candidate configs for diagnosis unless removal is needed',
            'if runtime smoke fails, set page momentum metadata enabled flags to false',
            'rerun Pest and inspect nginx/application logs',
        ],
    ];

    $report[] = 'Ready for runtime source hook: ' . ($ready ? 'yes' : 'no');
    $report[] = 'Discovered route files: ' . count($routeFiles);
    $report[] = 'Discovered menu files: ' . count($menuFiles);
    $report[] = 'Live mutation performed: no';
    $report[] = '';
    $report[] = '### Recommended patch';
    foreach ($plan['recommendedPatch'] as $step) {
        $report[] = '- ' . $step;
    }
    $report[] = '';
    $report[] = '### Rollback';
    foreach ($plan['rollback'] as $step) {
        $report[] = '- ' . $step;
    }

    if (!$ready) {
        $warnings++;
    }

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-runtime-source-hook-plan.json', json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-runtime-source-hook-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-runtime-source-hook-plan.log', "PAGE_ADMIN_MOMENTUM_RUNTIME_SOURCE_HOOK_PLAN_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_RUNTIME_SOURCE_HOOK_PLAN_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
