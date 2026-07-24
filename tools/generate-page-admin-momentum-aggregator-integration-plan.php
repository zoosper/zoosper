<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAggregatorIntegrationPlan;

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
$discoveryFile = $root . '/var/reports/admin-route-menu-aggregator-discovery.json';
$routeConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
$menuConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

$report[] = '## Page Admin Momentum Aggregator Integration Plan';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAggregatorIntegrationPlan::class)) {
    $report[] = 'Integration planner autoloadable: no';
    $errors++;
} elseif (!is_file($discoveryFile)) {
    $report[] = 'Discovery JSON missing. Run tools/discover-admin-route-menu-aggregators.php first.';
    $errors++;
} elseif (!is_file($routeConfigPath) || !is_file($menuConfigPath)) {
    $report[] = 'Route/menu metadata files missing.';
    $errors++;
} else {
    $discovery = json_decode((string) file_get_contents($discoveryFile), true);
    if (!is_array($discovery)) {
        $report[] = 'Discovery JSON could not be decoded.';
        $errors++;
    } else {
        $plan = (new PageMomentumAggregatorIntegrationPlan())->build(
            require $routeConfigPath,
            require $menuConfigPath,
            $discovery,
        );

        foreach ($plan as $key => $value) {
            if (is_bool($value)) {
                $report[] = '- ' . $key . ': ' . ($value ? 'yes' : 'no');
            } else {
                $report[] = '- ' . $key . ': ' . (is_scalar($value) ? (string) $value : json_encode($value));
            }
        }

        if (($plan['routeMetadataEnabled'] ?? false) !== true || ($plan['menuMetadataEnabled'] ?? false) !== true) {
            $errors++;
        }
        if (($plan['readyForPatchDraft'] ?? false) !== true) {
            $warnings++;
        }

        $reportDir = $root . '/var/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0775, true);
        }
        file_put_contents($reportDir . '/page-admin-momentum-aggregator-integration-plan.json', json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
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
file_put_contents($reportDir . '/page-admin-momentum-aggregator-integration-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-aggregator-integration-plan.log', "PAGE_ADMIN_MOMENTUM_AGGREGATOR_INTEGRATION_PLAN_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_AGGREGATOR_INTEGRATION_PLAN_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
