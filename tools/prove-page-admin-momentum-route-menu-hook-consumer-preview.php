<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminRouteMenuHook;
use Zoosper\Page\Admin\PageMomentumAdminRouteMenuHookConsumerPreview;

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
$runtimeConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
$hookCandidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';
$discoveryFile = $root . '/var/reports/admin-route-menu-aggregator-discovery.json';

$report[] = '## Page Momentum Route/Menu Hook Consumer Preview Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminRouteMenuHook::class) || !class_exists(PageMomentumAdminRouteMenuHookConsumerPreview::class)) {
    $report[] = 'Required hook/preview classes are not autoloadable.';
    $errors++;
} elseif (!is_file($runtimeConfigPath) || !is_file($hookCandidatePath)) {
    $report[] = 'Runtime config or hook candidate config missing.';
    $errors++;
} else {
    $runtimeConfig = require $runtimeConfigPath;
    $hookCandidate = require $hookCandidatePath;
    $hookExport = (new PageMomentumAdminRouteMenuHook())->export(
        is_array($runtimeConfig) ? $runtimeConfig : [],
        is_array($hookCandidate) ? $hookCandidate : [],
    );
    $discovery = is_file($discoveryFile) ? json_decode((string) file_get_contents($discoveryFile), true) : [];
    $preview = (new PageMomentumAdminRouteMenuHookConsumerPreview())->preview(
        $hookExport,
        is_array($discovery) ? $discovery : [],
    );

    foreach ($preview['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }

    $report[] = 'Ready for consumer patch: ' . ($preview['readyForConsumerPatch'] ? 'yes' : 'no');
    $report[] = 'Preview route count: ' . $preview['routeCount'];
    $report[] = 'Preview menu count: ' . $preview['menuCount'];
    $report[] = 'Discovered route files: ' . count($preview['routeFilesDiscovered']);
    $report[] = 'Discovered menu files: ' . count($preview['menuFilesDiscovered']);
    $report[] = 'Live mutation performed: ' . ($preview['liveMutation'] ? 'yes' : 'no');

    if (!$preview['readyForConsumerPatch']) {
        $warnings++;
    }

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-route-menu-hook-consumer-preview.json', json_encode($preview, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-route-menu-hook-consumer-preview.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-route-menu-hook-consumer-preview.log', "PAGE_ADMIN_MOMENTUM_ROUTE_MENU_HOOK_CONSUMER_PREVIEW_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_ROUTE_MENU_HOOK_CONSUMER_PREVIEW_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
