<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];

$requiredFiles = [
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'app/zoosper-page/src/Admin/PageMomentumDefinitionProvider.php',
    'app/zoosper-page/config/admin_page_momentum.php',
    'app/zoosper-page/config/admin_page_momentum_routes.php',
    'app/zoosper-page/config/admin_page_momentum_menu.php',
    'app/zoosper-page/resources/views/admin/page-momentum.latte',
    'tools/prove-page-admin-momentum-controller.php',
    'tools/prove-page-admin-momentum-definition-provider.php',
    'tools/audit-page-admin-momentum-runtime-bridge-readiness.php',
    'tools/audit-page-admin-momentum-phase-146-closure.php',
    'docs/development/page-admin-momentum-wiring-readiness.md',
    'docs/development/page-admin-momentum-runtime-bridge-readiness.md',
    'docs/development/page-admin-momentum-phase-1.46-closure.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.46i-z.md',
];

$report[] = '## Phase 1.46 Page Admin Momentum Closure Audit';
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
$report[] = 'Runtime route registered: no';
$report[] = 'Admin menu enabled: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-phase-146-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-phase-146-closure.log', "PAGE_ADMIN_MOMENTUM_PHASE_146_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_PHASE_146_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
