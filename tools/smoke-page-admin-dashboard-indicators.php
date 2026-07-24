<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageAdminDashboardIndicatorProvider;
use Zoosper\Page\Admin\PageAdminLaunchReadinessProvider;

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

$report[] = '## Page Admin Dashboard Indicators Smoke';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageAdminDashboardIndicatorProvider::class) || !class_exists(PageAdminLaunchReadinessProvider::class)) {
    $report[] = 'Dashboard indicator classes autoloadable: no';
    $errors++;
} else {
    $indicators = (new PageAdminDashboardIndicatorProvider())->indicators();
    $sections = (new PageAdminLaunchReadinessProvider())->sections();

    $checks = [
        'six indicators' => count($indicators) === 6,
        'six dashboard sections still present' => count($sections) === 6,
        'contains page CRUD readiness' => in_array('Page CRUD readiness', array_column($indicators, 'label'), true),
        'contains preview readiness' => in_array('Preview/readiness status', array_column($indicators, 'label'), true),
        'contains sidebar menu health' => in_array('Sidebar/menu health', array_column($indicators, 'label'), true),
        'contains route controller consistency' => in_array('Route/controller consistency', array_column($indicators, 'label'), true),
    ];

    foreach ($checks as $label => $passed) {
        $report[] = '- ' . $label . ': ' . ($passed ? 'yes' : 'no');
        if (!$passed) {
            $errors++;
        }
    }
}

$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-dashboard-indicators-smoke.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-dashboard-indicators-smoke.log', "PAGE_ADMIN_DASHBOARD_INDICATORS_SMOKE_WARNINGS {$warnings}\nPAGE_ADMIN_DASHBOARD_INDICATORS_SMOKE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
