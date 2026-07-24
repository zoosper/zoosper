<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumDefinitionProvider;

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

$report[] = '## Page Admin Momentum Definition Provider Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumDefinitionProvider::class)) {
    $report[] = 'Provider autoloadable: no';
    $errors++;
} elseif (!is_file($routeConfigPath) || !is_file($menuConfigPath)) {
    $report[] = 'Route/menu metadata files missing.';
    $errors++;
} else {
    $provider = new PageMomentumDefinitionProvider();
    $definitions = $provider->definitions(require $routeConfigPath, require $menuConfigPath);

    $route = $definitions['routes'][0] ?? [];
    $menu = $definitions['menuItems'][0] ?? [];

    $valid = $definitions['enabled'] === false
        && is_array($route)
        && is_array($menu)
        && ($route['name'] ?? '') === 'admin.page_momentum.index'
        && ($route['controller'] ?? '') === 'Zoosper\\Page\\Admin\\Controller\\PageMomentumAdminController'
        && ($route['action'] ?? '') === 'index'
        && ($menu['route'] ?? '') === 'admin.page_momentum.index';

    $report[] = 'Provider autoloadable: yes';
    $report[] = 'Runtime definitions enabled: ' . ($definitions['enabled'] ? 'yes' : 'no');
    $report[] = 'Route definitions: ' . count($definitions['routes']);
    $report[] = 'Menu item definitions: ' . count($definitions['menuItems']);
    $report[] = 'Definitions internally consistent: ' . ($valid ? 'yes' : 'no');

    if (!$valid) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Runtime route registered: no';
$report[] = 'Admin menu enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-definition-provider-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-definition-provider-proof.log', "PAGE_ADMIN_MOMENTUM_DEFINITION_PROVIDER_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
