<?php

declare(strict_types=1);

/**
 * Guarded RoleAdminController Latte cutover executor.
 *
 * Default mode is read-only. Apply mode is intentionally conservative and refuses
 * unknown source patterns rather than performing a risky blind rewrite.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
$apply = false;

foreach ($argv as $argument) {
    if ($argument === '--apply') {
        $apply = true;
    }

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
$indexTemplate = 'app/zoosper-core/views/admin/roles/index.latte';
$formTemplate = 'app/zoosper-core/views/admin/roles/form.latte';
$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-latte-cutover-executor.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-latte-cutover-executor.log';

$errors = [];
$warnings = [];
$actions = [];

if (! is_file($controllerPath)) {
    $errors[] = 'Controller not found: ' . $controllerRelative;
}

foreach ([$indexTemplate, $formTemplate] as $template) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $template))) {
        $errors[] = 'Required role template missing: ' . $template;
    }
}

$source = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$signals = sourceSignals($source);
$adminLayoutPath = findFile($root . DIRECTORY_SEPARATOR . 'app', 'AdminLayout.php');
$adminLayoutSource = $adminLayoutPath !== null ? (string) file_get_contents($adminLayoutPath) : '';
$layoutMethods = publicMethods($adminLayoutSource);
$controllerMethods = publicMethods($source);
$constructorParams = constructorParams($source);

$safePattern = detectSafePattern($source, $layoutMethods);
$applied = false;
$backupPath = null;

if ($safePattern === null) {
    $warnings[] = 'No recognised safe cutover pattern detected. The executor will not modify source.';
}

if ($apply && $errors === [] && $safePattern === null) {
    $errors[] = 'Apply refused: no recognised safe cutover pattern detected.';
}

if ($apply && $errors === [] && $safePattern !== null) {
    $backupPath = $controllerPath . '.phase-1.38.bak';
    copy($controllerPath, $backupPath);
    $newSource = applyPattern($source, $safePattern, $actions);

    if ($newSource === $source) {
        $errors[] = 'Apply refused: pattern produced no source changes.';
    } else {
        file_put_contents($controllerPath, $newSource);
        $applied = true;
    }
}

$report = [];
$report[] = '# RoleAdminController Latte Cutover Executor';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Mode: ' . ($apply ? 'apply' : 'read-only');
$report[] = 'Controller: ' . $controllerRelative;
$report[] = 'AdminLayout path: ' . ($adminLayoutPath ?? 'not found');
$report[] = 'Safe pattern: ' . ($safePattern ?? 'none');
$report[] = 'Applied: ' . ($applied ? 'yes' : 'no');
$report[] = 'Backup: ' . ($backupPath ?? 'none');
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = '';
$report[] = '## Controller public methods';
foreach ($controllerMethods as $method) {
    $report[] = '- ' . $method;
}
$report[] = '';
$report[] = '## Constructor parameters';
foreach ($constructorParams as $param) {
    $report[] = '- ' . $param;
}
$report[] = '';
$report[] = '## AdminLayout public methods';
foreach ($layoutMethods as $method) {
    $report[] = '- ' . $method;
}
$report[] = '';
$report[] = '## Source signals';
foreach ($signals as $name => $value) {
    $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
}

if ($actions !== []) {
    $report[] = '';
    $report[] = '## Actions';
    foreach ($actions as $action) {
        $report[] = '- ' . $action;
    }
}

if ($warnings !== []) {
    $report[] = '';
    $report[] = '## Warnings';
    foreach ($warnings as $warning) {
        $report[] = '- ' . $warning;
    }
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

$report[] = '';
$report[] = '## Next step';
$report[] = $applied
    ? 'Run full Pest and then the strict closeout gate.'
    : 'Use this report and the exported context to add the exact source-specific safe pattern or hand patch.';

file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Role admin latte cutover executor report written to: ' . $reportPath;
$log[] = 'MODE ' . ($apply ? 'apply' : 'read-only');
$log[] = 'SAFE_PATTERN ' . ($safePattern ?? 'none');
$log[] = 'APPLIED ' . ($applied ? 'yes' : 'no');
$log[] = 'CUTOVER_ERRORS ' . count($errors);
$log[] = 'CUTOVER_WARNINGS ' . count($warnings);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

if ($apply && $errors !== []) {
    exit(1);
}

if (! $apply && ! is_file($controllerPath)) {
    exit(1);
}

exit(0);

/** @return array<string,bool> */
function sourceSignals(string $source): array
{
    return [
        'contains_form_markup' => str_contains($source, '<form'),
        'contains_table_markup' => str_contains($source, '<table'),
        'contains_input_markup' => str_contains($source, '<input'),
        'contains_heredoc' => str_contains($source, '<<<'),
        'mentions_csrf' => stripos($source, 'csrf') !== false,
        'mentions_role' => stripos($source, 'role') !== false,
        'mentions_admin_layout' => str_contains($source, 'AdminLayout'),
    ];
}

/** @return list<string> */
function publicMethods(string $source): array
{
    if (! preg_match_all('/public\s+function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $source, $matches)) {
        return [];
    }

    return array_values(array_unique($matches[1]));
}

/** @return list<string> */
function constructorParams(string $source): array
{
    if (! preg_match('/function\s+__construct\s*\((.*?)\)/s', $source, $match)) {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $match[1]))));
}

function findFile(string $directory, string $filename): ?string
{
    if (! is_dir($directory)) {
        return null;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $filename) {
            return $file->getPathname();
        }
    }

    return null;
}

/** @param list<string> $layoutMethods */
function detectSafePattern(string $source, array $layoutMethods): ?string
{
    if ($source === '') {
        return null;
    }

    // Reserved for exact source-specific patterns. Current phase deliberately
    // refuses until the exact source shape is encoded, avoiding blind rewrites.
    return null;
}

/** @param list<string> $actions */
function applyPattern(string $source, string $safePattern, array &$actions): string
{
    $actions[] = 'No built-in pattern applied for: ' . $safePattern;
    return $source;
}
