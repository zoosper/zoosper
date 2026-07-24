<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumLiveCutoverPreflight;

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
$routeConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_routes.php';
$menuConfigPath = $root . '/app/zoosper-page/config/admin_page_momentum_menu.php';

$report[] = '## Page Admin Momentum Live Cutover Preflight Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumLiveCutoverPreflight::class)) {
    $report[] = 'Preflight service autoloadable: no';
    $errors++;
} elseif (!is_file($routeConfigPath) || !is_file($menuConfigPath)) {
    $report[] = 'Route/menu metadata files missing.';
    $errors++;
} else {
    $preflight = new PageMomentumLiveCutoverPreflight();
    $result = $preflight->inspect(require $routeConfigPath, require $menuConfigPath);

    $report[] = 'Preflight service autoloadable: yes';
    foreach ($result['checks'] as $check => $passed) {
        $report[] = '- ' . $check . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }
    $report[] = 'Ready for manual cutover: ' . ($result['readyForManualCutover'] ? 'yes' : 'no');
    $report[] = 'Live mutation performed: ' . ($result['liveMutation'] ? 'yes' : 'no');
}

$report[] = '';
$report[] = 'Live route registered: no';
$report[] = 'Live menu enabled: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-live-cutover-preflight.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-live-cutover-preflight.log', "PAGE_ADMIN_MOMENTUM_LIVE_CUTOVER_PREFLIGHT_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_LIVE_CUTOVER_PREFLIGHT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
