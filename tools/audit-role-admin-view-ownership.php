<?php

declare(strict_types=1);

/**
 * Strict ownership audit for the RoleAdminController view cutover.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$controllerRelative = 'app/zoosper-admin/src/Controller/RoleAdminController.php';
$controllerPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $controllerRelative);
$views = [
    'index' => 'app/zoosper-admin/resources/views/admin/roles/index.php',
    'form' => 'app/zoosper-admin/resources/views/admin/roles/form.php',
    'permission-tree' => 'app/zoosper-admin/resources/views/admin/roles/permission-tree.php',
    'user-assignment' => 'app/zoosper-admin/resources/views/admin/roles/user-assignment.php',
];

$errors = [];
$controllerSource = '';
if (! is_file($controllerPath)) {
    $errors[] = 'Controller missing: ' . $controllerRelative;
} else {
    $controllerSource = (string) file_get_contents($controllerPath);
}

$controllerSignals = [
    'contains_form' => str_contains($controllerSource, '<form'),
    'contains_table' => str_contains($controllerSource, '<table'),
    'contains_input' => str_contains($controllerSource, '<input'),
    'contains_label' => str_contains($controllerSource, '<label'),
    'contains_heredoc' => str_contains($controllerSource, '<<<'),
    'has_render_role_view' => str_contains($controllerSource, 'function renderRoleView('),
];

foreach (['contains_form', 'contains_table', 'contains_input', 'contains_label', 'contains_heredoc'] as $signal) {
    if ($controllerSignals[$signal]) {
        $errors[] = 'Controller still owns markup signal: ' . $signal;
    }
}

if (! $controllerSignals['has_render_role_view']) {
    $errors[] = 'Controller missing renderRoleView seam.';
}

$viewSignals = [];
foreach ($views as $name => $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (! is_file($path)) {
        $errors[] = 'View missing: ' . $relative;
        $viewSignals[$name] = ['exists' => false];
        continue;
    }

    $contents = (string) file_get_contents($path);
    $viewSignals[$name] = [
        'exists' => true,
        'contains_form' => str_contains($contents, '<form'),
        'contains_table' => str_contains($contents, '<table'),
        'contains_input' => str_contains($contents, '<input'),
        'contains_label' => str_contains($contents, '<label'),
    ];
}

if (($viewSignals['index']['exists'] ?? false) && ! ($viewSignals['index']['contains_table'] ?? false)) {
    $errors[] = 'Index view does not own table markup.';
}

if (($viewSignals['form']['exists'] ?? false) && ! (($viewSignals['form']['contains_form'] ?? false) && ($viewSignals['form']['contains_input'] ?? false))) {
    $errors[] = 'Form view does not own form/input markup.';
}

foreach (['permission-tree', 'user-assignment'] as $viewName) {
    if (($viewSignals[$viewName]['exists'] ?? false) && ! (($viewSignals[$viewName]['contains_input'] ?? false) && ($viewSignals[$viewName]['contains_label'] ?? false))) {
        $errors[] = $viewName . ' view does not own input/label markup.';
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-view-ownership.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-view-ownership.log';

$report = [];
$report[] = '# Role Admin View Ownership Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Controller: ' . $controllerRelative;
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Controller signals';
foreach ($controllerSignals as $name => $value) {
    $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
}
$report[] = '';
$report[] = '## View signals';
foreach ($viewSignals as $view => $signals) {
    $report[] = '';
    $report[] = '### ' . $view;
    foreach ($signals as $name => $value) {
        $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
    }
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Role admin view ownership report written to: ' . $reportPath;
$log[] = 'CONTROLLER_MARKUP ' . (count(array_filter(array_intersect_key($controllerSignals, array_flip(['contains_form','contains_table','contains_input','contains_label','contains_heredoc'])))) === 0 ? 'clean' : 'dirty');
$log[] = 'RENDER_ROLE_VIEW ' . ($controllerSignals['has_render_role_view'] ? 'yes' : 'no');
$log[] = 'VIEW_OWNERSHIP_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
