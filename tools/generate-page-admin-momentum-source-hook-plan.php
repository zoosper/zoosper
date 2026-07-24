<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];
$previewFile = $root . '/var/reports/page-admin-momentum-hook-consumer-preview.json';
$discoveryFile = $root . '/var/reports/admin-route-menu-aggregator-discovery.json';

$report[] = '## Page Admin Momentum Source Hook Plan';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($previewFile)) {
    $report[] = 'Hook consumer preview JSON missing. Run tools/prove-page-admin-momentum-hook-consumer-preview.php first.';
    $errors++;
} else {
    $preview = json_decode((string) file_get_contents($previewFile), true);
    $ready = is_array($preview) && ($preview['readyForSourceHook'] ?? false) === true;
    $discovery = is_file($discoveryFile) ? json_decode((string) file_get_contents($discoveryFile), true) : [];
    $routeFiles = is_array($discovery) && isset($discovery['routeFiles']) && is_array($discovery['routeFiles']) ? $discovery['routeFiles'] : [];
    $menuFiles = is_array($discovery) && isset($discovery['menuFiles']) && is_array($discovery['menuFiles']) ? $discovery['menuFiles'] : [];

    $plan = [
        'readyForSourceHook' => $ready,
        'liveMutation' => false,
        'routeFilesDiscovered' => $routeFiles,
        'menuFilesDiscovered' => $menuFiles,
        'recommendedPatch' => [
            'inspect discovered route and menu aggregation files before editing',
            'load or include app/zoosper-page/config/admin_page_momentum_hook_candidate.php from the owning page module',
            'append the single admin.page_momentum.index route if not already registered',
            'append the single Page momentum menu item if not already registered',
            'guard both route and menu with page.manage',
            'smoke /admin and /admin/page-momentum, then run full Pest suite',
        ],
        'rollback' => [
            'remove the source-level hook added in the next phase',
            'remove app/zoosper-page/config/admin_page_momentum_hook_candidate.php only if needed',
            'leave page momentum metadata active unless runtime smoke fails',
            'if runtime smoke fails, set page_momentum, page_momentum_routes, and page_momentum_menu enabled flags to false',
            'rerun Pest and inspect nginx/application logs',
        ],
    ];

    $report[] = 'Ready for source hook: ' . ($ready ? 'yes' : 'no');
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
    file_put_contents($reportDir . '/page-admin-momentum-source-hook-plan.json', json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-source-hook-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-source-hook-plan.log', "PAGE_ADMIN_MOMENTUM_SOURCE_HOOK_PLAN_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_SOURCE_HOOK_PLAN_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
