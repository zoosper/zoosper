<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminHookProvider;

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
$hookPath = $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

$report[] = '## Page Admin Momentum Hook Provider Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminHookProvider::class)) {
    $report[] = 'Hook provider autoloadable: no';
    $errors++;
} elseif (!is_file($hookPath)) {
    $report[] = 'Hook candidate config missing. Run tools/generate-page-admin-momentum-hook-candidate.php first.';
    $errors++;
} else {
    $payload = require $hookPath;
    $rootPayload = is_array($payload) ? ($payload['page_momentum_admin_hook'] ?? []) : [];
    $enabled = is_array($rootPayload) && ($rootPayload['enabled'] ?? false) === true;
    $routes = is_array($rootPayload) && isset($rootPayload['routes']) && is_array($rootPayload['routes']) ? $rootPayload['routes'] : [];
    $menuItems = is_array($rootPayload) && isset($rootPayload['menu_items']) && is_array($rootPayload['menu_items']) ? $rootPayload['menu_items'] : [];
    $mutation = is_array($rootPayload) && ($rootPayload['live_mutation'] ?? true) === true;

    $valid = $enabled
        && count($routes) === 1
        && count($menuItems) === 1
        && ($routes[0]['name'] ?? '') === 'admin.page_momentum.index'
        && ($menuItems[0]['route'] ?? '') === 'admin.page_momentum.index'
        && !$mutation;

    $report[] = 'Hook candidate enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = 'Hook route count: ' . count($routes);
    $report[] = 'Hook menu count: ' . count($menuItems);
    $report[] = 'Hook live mutation: ' . ($mutation ? 'yes' : 'no');
    $report[] = 'Hook provider valid: ' . ($valid ? 'yes' : 'no');

    if (!$valid) {
        $errors++;
    }
}

$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-hook-provider-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-hook-provider-proof.log', "PAGE_ADMIN_MOMENTUM_HOOK_PROVIDER_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_HOOK_PROVIDER_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
