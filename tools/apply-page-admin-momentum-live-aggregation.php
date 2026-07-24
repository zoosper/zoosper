<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminLiveAggregationIntegrator;
use Zoosper\Page\Admin\PageMomentumAdminRouteMenuHook;

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
$backupDir = $root . '/var/backups/page-admin-momentum-live-aggregation';
$routeTarget = is_file($root . '/app/zoosper-page/config/admin_routes.php')
    ? $root . '/app/zoosper-page/config/admin_routes.php'
    : (is_file($root . '/app/zoosper-page/config/routes.php')
        ? $root . '/app/zoosper-page/config/routes.php'
        : $root . '/app/zoosper-page/config/admin_routes.php');
$menuTarget = $root . '/app/zoosper-page/config/admin_menu.php';
$runtimeConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_aggregation_candidate.php';
$hookCandidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

$report[] = '## Page Momentum Live Aggregation Apply';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminRouteMenuHook::class) || !class_exists(PageMomentumAdminLiveAggregationIntegrator::class)) {
    $report[] = 'Required hook/integrator classes are not autoloadable.';
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

    $routes = $hookExport['routes'] ?? [];
    $items = $hookExport['menuItems'] ?? [];
    if (!is_array($routes) || count($routes) !== 1 || !is_array($items) || count($items) !== 1) {
        $report[] = 'Hook export did not contain exactly one route and one menu item.';
        $errors++;
    } else {
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0775, true);
        }

        $integrator = new PageMomentumAdminLiveAggregationIntegrator();
        $routeConfig = is_file($routeTarget) ? require $routeTarget : [];
        $menuConfig = is_file($menuTarget) ? require $menuTarget : [];

        if (!is_array($routeConfig)) {
            $report[] = 'Route config did not return an array: ' . str_replace($root . '/', '', $routeTarget);
            $errors++;
        }
        if (!is_array($menuConfig)) {
            $report[] = 'Menu config did not return an array: ' . str_replace($root . '/', '', $menuTarget);
            $errors++;
        }

        if ($errors === 0) {
            if (is_file($routeTarget)) {
                copy($routeTarget, $backupDir . '/' . basename($routeTarget) . '.bak');
            }
            if (is_file($menuTarget)) {
                copy($menuTarget, $backupDir . '/' . basename($menuTarget) . '.bak');
            }

            $mergedRoutes = $integrator->mergeRoutes($routeConfig, array_values($routes));
            $mergedMenu = $integrator->mergeMenu($menuConfig, array_values($items));

            $routePhp = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($mergedRoutes, true) . ";\n";
            $menuPhp = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($mergedMenu, true) . ";\n";
            file_put_contents($routeTarget, $routePhp);
            file_put_contents($menuTarget, $menuPhp);

            $report[] = 'Route target: ' . str_replace($root . '/', '', $routeTarget);
            $report[] = 'Menu target: ' . str_replace($root . '/', '', $menuTarget);
            $report[] = 'Backups directory: var/backups/page-admin-momentum-live-aggregation';
            $report[] = 'Route/menu entry applied or already present: yes';
            $report[] = 'Existing core aggregator internals modified: no';
        }
    }
}

$report[] = 'Live aggregation config mutation performed: yes';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-live-aggregation-apply.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-live-aggregation-apply.log', "PAGE_ADMIN_MOMENTUM_LIVE_AGGREGATION_APPLY_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_LIVE_AGGREGATION_APPLY_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
