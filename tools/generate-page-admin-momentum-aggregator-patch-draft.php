<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAggregatorPatchDraft;

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
$planFile = $root . '/var/reports/page-admin-momentum-aggregator-integration-plan.json';
$routeConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
$menuConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

$report[] = '## Page Admin Momentum Aggregator Patch Draft';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAggregatorPatchDraft::class)) {
    $report[] = 'Patch draft planner autoloadable: no';
    $errors++;
} elseif (!is_file($planFile)) {
    $report[] = 'Integration plan JSON missing. Run tools/generate-page-admin-momentum-aggregator-integration-plan.php first.';
    $errors++;
} elseif (!is_file($routeConfigPath) || !is_file($menuConfigPath)) {
    $report[] = 'Route/menu metadata files missing.';
    $errors++;
} else {
    $plan = json_decode((string) file_get_contents($planFile), true);
    if (!is_array($plan)) {
        $report[] = 'Integration plan JSON could not be decoded.';
        $errors++;
    } else {
        $draft = (new PageMomentumAggregatorPatchDraft())->draft(
            $plan,
            require $routeConfigPath,
            require $menuConfigPath,
        );

        $report[] = 'Ready for patch draft: ' . (($draft['readyForPatchDraft'] ?? false) ? 'yes' : 'no');
        $report[] = 'Route: ' . ($draft['routeMethod'] ?? '') . ' ' . ($draft['routePath'] ?? '');
        $report[] = 'Controller: ' . ($draft['routeController'] ?? '') . '::' . ($draft['routeAction'] ?? '');
        $report[] = 'Permission: ' . ($draft['routePermission'] ?? '');
        $report[] = 'Menu label: ' . ($draft['menuLabel'] ?? '');
        $report[] = 'Live mutation performed: ' . (($draft['liveMutation'] ?? true) ? 'yes' : 'no');
        $report[] = '';
        $report[] = '### Recommended patch steps';
        foreach (($draft['recommendedPatch'] ?? []) as $step) {
            $report[] = '- ' . $step;
        }
        $report[] = '';
        $report[] = '### Rollback';
        foreach (($draft['rollback'] ?? []) as $step) {
            $report[] = '- ' . $step;
        }

        if (($draft['readyForPatchDraft'] ?? false) !== true) {
            $warnings++;
        }
        if (($draft['liveMutation'] ?? true) !== false) {
            $errors++;
        }

        $reportDir = $root . '/var/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0775, true);
        }
        file_put_contents($reportDir . '/page-admin-momentum-aggregator-patch-draft.json', json_encode($draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }
}

$report[] = '';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-aggregator-patch-draft.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-aggregator-patch-draft.log', "PAGE_ADMIN_MOMENTUM_AGGREGATOR_PATCH_DRAFT_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_AGGREGATOR_PATCH_DRAFT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
