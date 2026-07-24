<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAggregatorPatchBuilder;

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
$candidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';

$report[] = '## Page Admin Momentum Aggregator Candidate Apply';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAggregatorPatchBuilder::class)) {
    $report[] = 'Patch builder autoloadable: no';
    $errors++;
} elseif (!is_file($routeConfigPath) || !is_file($menuConfigPath)) {
    $report[] = 'Route/menu metadata files missing.';
    $errors++;
} else {
    $candidate = (new PageMomentumAggregatorPatchBuilder())->buildCandidate(
        require $routeConfigPath,
        require $menuConfigPath,
    );

    $enabled = (bool) ($candidate['page_momentum_admin_integration']['enabled'] ?? false);
    $routes = $candidate['page_momentum_admin_integration']['routes'] ?? [];
    $items = $candidate['page_momentum_admin_integration']['menu_items'] ?? [];

    if (!$enabled || !is_array($routes) || count($routes) !== 1 || !is_array($items) || count($items) !== 1) {
        $warnings++;
    }

    $export = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($candidate, true) . ";\n";
    file_put_contents($candidatePath, $export);

    $report[] = 'Candidate config written: app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';
    $report[] = 'Candidate enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = 'Candidate route count: ' . (is_array($routes) ? count($routes) : 0);
    $report[] = 'Candidate menu count: ' . (is_array($items) ? count($items) : 0);
    $report[] = 'Existing aggregator files overwritten: no';
    $report[] = 'Live mutation performed: no';

    $reportDir = $root . '/var/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/page-admin-momentum-aggregator-candidate.json', json_encode($candidate, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report[] = '';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-aggregator-candidate.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-aggregator-candidate.log', "PAGE_ADMIN_MOMENTUM_AGGREGATOR_CANDIDATE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_AGGREGATOR_CANDIDATE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
