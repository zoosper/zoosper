<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];

$requiredFiles = [
    'tools/audit-page-admin-visible-momentum.php',
    'tools/write-page-admin-visible-momentum-plan.php',
    'tools/audit-page-admin-visible-momentum-closure.php',
    'tools/audit-page-admin-route-menu-conventions.php',
    'tools/write-page-admin-momentum-wiring-plan.php',
    'tools/audit-page-admin-momentum-phase-145-closure.php',
    'app/zoosper-page/config/admin_page_momentum.php',
    'app/zoosper-page/config/admin_page_momentum_routes.php',
    'app/zoosper-page/config/admin_page_momentum_menu.php',
    'app/zoosper-page/resources/views/admin/page-momentum.latte',
    'docs/development/page-admin-visible-momentum.md',
    'docs/development/page-admin-momentum-phase-1.45-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.45i-z.md',
];

$report[] = '## Phase 1.45 Visible Page Admin Momentum Closure Audit';
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

foreach ([
    'app/zoosper-page/config/admin_page_momentum.php' => ['page_momentum', 'enabled'],
    'app/zoosper-page/config/admin_page_momentum_routes.php' => ['page_momentum_routes', 'enabled'],
    'app/zoosper-page/config/admin_page_momentum_menu.php' => ['page_momentum_menu', 'enabled'],
] as $file => [$rootKey, $enabledKey]) {
    $path = $root . '/' . $file;
    if (!is_file($path)) {
        continue;
    }
    $config = require $path;
    $enabled = (bool) ($config[$rootKey][$enabledKey] ?? true);
    $report[] = '- ' . $file . ' enabled: ' . ($enabled ? 'yes' : 'no');
    if ($enabled) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Runtime route changed: no';
$report[] = 'Admin menu changed: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-145-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-145-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_145_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_145_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
