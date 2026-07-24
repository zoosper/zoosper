<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumRuntimeBridge;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}
require $autoload;

$errors = 0;
$report = [];
$routeConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
$menuConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

$report[] = '## Page Admin Momentum Runtime Bridge Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumRuntimeBridge::class)) {
    $report[] = 'Runtime bridge autoloadable: no';
    $errors++;
} elseif (!is_file($routeConfigPath) || !is_file($menuConfigPath)) {
    $report[] = 'Route/menu metadata files missing.';
    $errors++;
} else {
    $bridge = new PageMomentumRuntimeBridge();
    $routeConfig = require $routeConfigPath;
    $menuConfig = require $menuConfigPath;

    $disabled = $bridge->definitions($routeConfig, $menuConfig);

    $fixtureRouteConfig = $routeConfig;
    $fixtureMenuConfig = $menuConfig;
    $fixtureRouteConfig['page_momentum_routes']['enabled'] = true;
    $fixtureMenuConfig['page_momentum_menu']['enabled'] = true;
    $enabled = $bridge->definitions($fixtureRouteConfig, $fixtureMenuConfig);

    $disabledOk = $disabled['routeCount'] === 0 && $disabled['menuCount'] === 0;
    $enabledOk = $enabled['routeCount'] === 1
        && $enabled['menuCount'] === 1
        && ($enabled['routes'][0]['name'] ?? '') === 'admin.page_momentum.index'
        && ($enabled['menuItems'][0]['route'] ?? '') === 'admin.page_momentum.index';

    $report[] = 'Runtime bridge autoloadable: yes';
    $report[] = 'Disabled metadata exports routes: ' . $disabled['routeCount'];
    $report[] = 'Disabled metadata exports menu items: ' . $disabled['menuCount'];
    $report[] = 'Fixture-enabled metadata exports routes: ' . $enabled['routeCount'];
    $report[] = 'Fixture-enabled metadata exports menu items: ' . $enabled['menuCount'];
    $report[] = 'Disabled default proof: ' . ($disabledOk ? 'yes' : 'no');
    $report[] = 'Fixture-enabled export proof: ' . ($enabledOk ? 'yes' : 'no');

    if (!$disabledOk || !$enabledOk) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Live route registered: no';
$report[] = 'Live menu enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-runtime-bridge-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-runtime-bridge-proof.log', "PAGE_ADMIN_MOMENTUM_RUNTIME_BRIDGE_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
