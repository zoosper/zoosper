<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAdminAggregationBridge;
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
$candidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';
$hookPath = $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php';

$report[] = '## Page Admin Momentum Hook Candidate Generator';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminAggregationBridge::class) || !class_exists(PageMomentumAdminHookProvider::class)) {
    $report[] = 'Required bridge/hook provider classes are not autoloadable.';
    $errors++;
} elseif (!is_file($candidatePath)) {
    $report[] = 'Runtime candidate config missing. Run tools/apply-page-admin-momentum-aggregator-candidate.php first.';
    $errors++;
} else {
    $candidate = require $candidatePath;
    $bridgeExport = (new PageMomentumAdminAggregationBridge())->export(is_array($candidate) ? $candidate : []);
    $payload = (new PageMomentumAdminHookProvider())->payload($bridgeExport);

    $hookRoot = $payload['page_momentum_admin_hook'];
    $enabled = (bool) ($hookRoot['enabled'] ?? false);
    $routes = is_array($hookRoot['routes'] ?? null) ? $hookRoot['routes'] : [];
    $menuItems = is_array($hookRoot['menu_items'] ?? null) ? $hookRoot['menu_items'] : [];
    $mutation = (bool) ($hookRoot['live_mutation'] ?? true);

    if (!$enabled || count($routes) !== 1 || count($menuItems) !== 1 || $mutation) {
        $warnings++;
    }

    $export = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($payload, true) . ";\n";
    file_put_contents($hookPath, $export);

    $report[] = 'Hook candidate written: app/zoosper-page/config/admin_page_momentum_hook_candidate.php';
    $report[] = 'Hook candidate enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = 'Hook route count: ' . count($routes);
    $report[] = 'Hook menu count: ' . count($menuItems);
    $report[] = 'Hook live mutation: ' . ($mutation ? 'yes' : 'no');

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-hook-candidate.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-hook-candidate.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-hook-candidate.log', "PAGE_ADMIN_MOMENTUM_HOOK_CANDIDATE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_HOOK_CANDIDATE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
