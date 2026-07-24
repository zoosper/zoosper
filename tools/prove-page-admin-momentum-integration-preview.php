<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminIntegrationPreview;

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

$report[] = '## Page Admin Momentum Integration Preview Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminIntegrationPreview::class)) {
    $report[] = 'Integration preview autoloadable: no';
    $errors++;
} elseif (!is_file($routeConfigPath) || !is_file($menuConfigPath)) {
    $report[] = 'Route/menu metadata files missing.';
    $errors++;
} else {
    $previewer = new PageMomentumAdminIntegrationPreview();
    $routeConfig = require $routeConfigPath;
    $menuConfig = require $menuConfigPath;

    $disabledPreview = $previewer->preview($routeConfig, $menuConfig);

    $fixtureRouteConfig = $routeConfig;
    $fixtureMenuConfig = $menuConfig;
    $fixtureRouteConfig['page_momentum_routes']['enabled'] = true;
    $fixtureMenuConfig['page_momentum_menu']['enabled'] = true;
    $enabledPreview = $previewer->preview($fixtureRouteConfig, $fixtureMenuConfig);

    $disabledOk = $disabledPreview['routeCount'] === 0
        && $disabledPreview['menuCount'] === 0
        && $disabledPreview['liveMutation'] === false;
    $enabledOk = $enabledPreview['routeCount'] === 1
        && $enabledPreview['menuCount'] === 1
        && $enabledPreview['liveMutation'] === false
        && ($enabledPreview['wouldRegisterRoutes'][0]['name'] ?? '') === 'admin.page_momentum.index'
        && ($enabledPreview['wouldRegisterMenuItems'][0]['route'] ?? '') === 'admin.page_momentum.index';

    $report[] = 'Integration preview autoloadable: yes';
    $report[] = 'Disabled preview route count: ' . $disabledPreview['routeCount'];
    $report[] = 'Disabled preview menu count: ' . $disabledPreview['menuCount'];
    $report[] = 'Fixture-enabled preview route count: ' . $enabledPreview['routeCount'];
    $report[] = 'Fixture-enabled preview menu count: ' . $enabledPreview['menuCount'];
    $report[] = 'Disabled preview safe: ' . ($disabledOk ? 'yes' : 'no');
    $report[] = 'Fixture-enabled preview safe: ' . ($enabledOk ? 'yes' : 'no');

    if (!$disabledOk || !$enabledOk) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Live route registered: no';
$report[] = 'Live menu enabled: no';
$report[] = 'Live mutation performed: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-integration-preview-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-integration-preview-proof.log', "PAGE_ADMIN_MOMENTUM_INTEGRATION_PREVIEW_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
