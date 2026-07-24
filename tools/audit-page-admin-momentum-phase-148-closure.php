<?php

declare(strict_types=1);

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

$requiredFiles = [
    'app/zoosper-page/config/admin_page_momentum.php',
    'app/zoosper-page/config/admin_page_momentum_routes.php',
    'app/zoosper-page/config/admin_page_momentum_menu.php',
    'app/zoosper-page/src/Admin/PageMomentumActivationGuard.php',
    'tools/prove-page-admin-momentum-metadata-activation.php',
    'tools/audit-page-admin-momentum-live-smoke.php',
    'tools/audit-page-admin-momentum-phase-148-closure.php',
    'docs/development/page-admin-momentum-phase-1.48-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.48m-z.md',
];

$report[] = '## Phase 1.48 Page Admin Momentum Cutover Closure Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredFiles as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$configs = [
    'page momentum root enabled' => [$root . '/app/zoosper-page/config/admin_page_momentum.php', 'page_momentum'],
    'page momentum routes enabled' => [$root . '/app/zoosper-page/config/admin_page_momentum_routes.php', 'page_momentum_routes'],
    'page momentum menu enabled' => [$root . '/app/zoosper-page/config/admin_page_momentum_menu.php', 'page_momentum_menu'],
];
foreach ($configs as $label => [$path, $key]) {
    if (!is_file($path)) {
        continue;
    }
    $config = require $path;
    $enabled = (bool) ($config[$key]['enabled'] ?? false);
    $report[] = '- ' . $label . ': ' . ($enabled ? 'yes' : 'no');
    if (!$enabled) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Route path: /admin/page-momentum';
$report[] = 'Permission: page.manage';
$report[] = 'Rollback: set three metadata enabled flags to false';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-148-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-148-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_148_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_148_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
