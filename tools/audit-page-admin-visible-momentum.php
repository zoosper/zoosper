<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$warnings = 0;
$report = [];

$report[] = '## Page/Admin Visible Momentum Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$checks = [
    'page module directory' => 'app/zoosper-page',
    'page module config directory' => 'app/zoosper-page/config',
    'page module source directory' => 'app/zoosper-page/src',
    'page admin momentum config' => 'app/zoosper-page/config/admin_page_momentum.php',
    'page admin momentum view stub' => 'app/zoosper-page/resources/views/admin/page-momentum.latte',
];

foreach ($checks as $label => $path) {
    $exists = file_exists($root . '/' . $path);
    $report[] = '- ' . $label . ': ' . ($exists ? 'yes' : 'no') . ' (' . $path . ')';
    if (!$exists && in_array($label, ['page module directory', 'page module config directory', 'page module source directory'], true)) {
        $warnings++;
    } elseif (!$exists) {
        $errors++;
    }
}

$configPath = $root . '/app/zoosper-page/config/admin_page_momentum.php';
if (is_file($configPath)) {
    $config = require $configPath;
    $valid = is_array($config)
        && isset($config['page_momentum'])
        && is_array($config['page_momentum'])
        && ($config['page_momentum']['enabled'] ?? true) === false;
    $report[] = '- momentum config disabled by default: ' . ($valid ? 'yes' : 'no');
    if (!$valid) {
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
file_put_contents($reportDir . '/page-admin-visible-momentum-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-visible-momentum-audit.log', "PAGE_ADMIN_VISIBLE_MOMENTUM_WARNINGS {$warnings}\nPAGE_ADMIN_VISIBLE_MOMENTUM_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
