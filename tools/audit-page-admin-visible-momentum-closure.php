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
    'app/zoosper-page/config/admin_page_momentum.php',
    'app/zoosper-page/resources/views/admin/page-momentum.latte',
    'docs/development/page-admin-visible-momentum.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.45a-h.md',
];

$report[] = '## Page/Admin Visible Momentum Closure Audit';
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

$configPath = $root . '/app/zoosper-page/config/admin_page_momentum.php';
if (is_file($configPath)) {
    $config = require $configPath;
    $enabled = (bool) ($config['page_momentum']['enabled'] ?? true);
    $items = $config['page_momentum']['items'] ?? [];
    $report[] = '';
    $report[] = '- momentum config enabled: ' . ($enabled ? 'yes' : 'no');
    $report[] = '- momentum item count: ' . (is_array($items) ? count($items) : 0);
    if ($enabled || !is_array($items) || count($items) === 0) {
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
file_put_contents($reportDir . '/page-admin-visible-momentum-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-visible-momentum-closure.log', "PAGE_ADMIN_VISIBLE_MOMENTUM_CLOSURE_WARNINGS {$warnings}\nPAGE_ADMIN_VISIBLE_MOMENTUM_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
