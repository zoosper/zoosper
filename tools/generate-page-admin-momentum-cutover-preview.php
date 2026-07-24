<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminIntegrationPreview;
use Zoosper\Page\Admin\PageMomentumLiveCutoverPreflight;

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

$report[] = '## Page Admin Momentum Cutover Preview';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminIntegrationPreview::class) || !class_exists(PageMomentumLiveCutoverPreflight::class)) {
    $report[] = 'Required bridge/preflight classes are not autoloadable.';
    $errors++;
} elseif (!is_file($routeConfigPath) || !is_file($menuConfigPath)) {
    $report[] = 'Route/menu metadata files missing.';
    $errors++;
} else {
    $routeConfig = require $routeConfigPath;
    $menuConfig = require $menuConfigPath;

    $preflight = (new PageMomentumLiveCutoverPreflight())->inspect($routeConfig, $menuConfig);

    $enabledRouteConfig = $routeConfig;
    $enabledMenuConfig = $menuConfig;
    $enabledRouteConfig['page_momentum_routes']['enabled'] = true;
    $enabledMenuConfig['page_momentum_menu']['enabled'] = true;

    $preview = (new PageMomentumAdminIntegrationPreview())->preview($enabledRouteConfig, $enabledMenuConfig);

    $payload = [
        'preflightReady' => $preflight['readyForManualCutover'],
        'liveMutation' => false,
        'wouldRegisterRoutes' => $preview['wouldRegisterRoutes'],
        'wouldRegisterMenuItems' => $preview['wouldRegisterMenuItems'],
        'rollback' => [
            'revert enabled flags to false',
            'remove route/menu entries if manually wired',
            'rerun full Pest suite',
            'check nginx and var/log/exception.log',
        ],
    ];

    $report[] = 'Preflight ready: ' . ($payload['preflightReady'] ? 'yes' : 'no');
    $report[] = 'Would-register route count: ' . count($payload['wouldRegisterRoutes']);
    $report[] = 'Would-register menu count: ' . count($payload['wouldRegisterMenuItems']);
    $report[] = 'Live mutation performed: no';

    if (!$payload['preflightReady'] || count($payload['wouldRegisterRoutes']) !== 1 || count($payload['wouldRegisterMenuItems']) !== 1) {
        $errors++;
    }

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-cutover-preview.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Live route registered: no';
$report[] = 'Live menu enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-cutover-preview.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-cutover-preview.log', "PAGE_ADMIN_MOMENTUM_CUTOVER_PREVIEW_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
