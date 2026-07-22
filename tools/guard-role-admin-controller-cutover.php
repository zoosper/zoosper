<?php

declare(strict_types=1);

/**
 * Guarded RoleAdminController to Latte cutover harness.
 *
 * Default mode is read-only. Apply mode refuses unless a safe known source pattern is detected.
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

$controllerPath = findFile($root . DIRECTORY_SEPARATOR . 'app', 'RoleAdminController.php');
$indexTemplate = 'app/zoosper-core/views/admin/roles/index.latte';
$formTemplate = 'app/zoosper-core/views/admin/roles/form.latte';

$errors = [];
$warnings = [];

if ($controllerPath === null) {
    $errors[] = 'RoleAdminController.php was not found under app/.';
}

foreach ([$indexTemplate, $formTemplate] as $template) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $template))) {
        $errors[] = 'Required template missing: ' . $template;
    }
}

$source = $controllerPath !== null ? (string) file_get_contents($controllerPath) : '';
$publicMethods = publicMethods($source);
$constructorParams = constructorParams($source);
$inlineSignals = [
    'form' => str_contains($source, '<form'),
    'table' => str_contains($source, '<table'),
    'heredoc' => str_contains($source, '<<<'),
];
$renderSignals = discoverRenderSignals($root . DIRECTORY_SEPARATOR . 'app');
$safePattern = detectSafePattern($source, $renderSignals);

if (! $inlineSignals['form'] && ! $inlineSignals['table'] && ! $inlineSignals['heredoc']) {
    $warnings[] = 'No large inline form/table/heredoc signal was detected. Controller may already be partially migrated.';
}

if ($renderSignals === []) {
    $warnings[] = 'No render/view source signal was detected. Apply mode will refuse.';
}

$willApply = $apply && $errors === [] && $safePattern !== null;

if ($apply && $safePattern === null) {
    $errors[] = 'Apply refused: no recognised safe cutover pattern was detected for this controller.';
}

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-guarded-cutover.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-guarded-cutover.log';

$report = [];
$report[] = '# Guarded RoleAdminController Cutover Report';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Mode: ' . ($apply ? 'apply' : 'read-only');
$report[] = 'Repo root: ' . $root;
$report[] = 'Controller path: ' . ($controllerPath ?? 'not found');
$report[] = 'Safe pattern: ' . ($safePattern ?? 'none');
$report[] = 'Would apply: ' . ($willApply ? 'yes' : 'no');
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = '';

$report[] = '## Public methods';
$report[] = '';
foreach ($publicMethods as $method) {
    $report[] = '- ' . $method;
}
if ($publicMethods === []) {
    $report[] = '- none detected';
}

$report[] = '';
$report[] = '## Constructor parameters';
$report[] = '';
foreach ($constructorParams as $param) {
    $report[] = '- ' . $param;
}
if ($constructorParams === []) {
    $report[] = '- none detected';
}

$report[] = '';
$report[] = '## Inline signals';
$report[] = '';
foreach ($inlineSignals as $name => $value) {
    $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
}

$report[] = '';
$report[] = '## Render/view signals';
$report[] = '';
foreach ($renderSignals as $signal) {
    $report[] = '- ' . $signal;
}
if ($renderSignals === []) {
    $report[] = '- none detected';
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
$report[] = '## Apply behaviour';
$report[] = '';
$report[] = $willApply
    ? 'A recognised safe pattern was detected. Source would be changed by this tool.'
    : 'No source changes were made.';

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Guarded role admin cutover report written to: ' . $txtPath;
$log[] = 'MODE ' . ($apply ? 'apply' : 'read-only');
$log[] = 'CONTROLLER_FOUND ' . ($controllerPath !== null ? 'yes' : 'no');
$log[] = 'SAFE_PATTERN ' . ($safePattern ?? 'none');
$log[] = 'WOULD_APPLY ' . ($willApply ? 'yes' : 'no');
$log[] = 'CUTOVER_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

// This phase is allowed to pass in read-only mode even if no safe apply pattern exists.
if ($apply && $errors !== []) {
    exit(1);
}

if (! $apply && $controllerPath === null) {
    exit(1);
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

/** @return list<string> */
function discoverRenderSignals(string $directory): array
{
    if (! is_dir($directory)) {
        return [];
    }

    $signals = [];
    $needles = ['TemplateRenderer', 'TemplateView', 'Latte', 'render(', 'ViewRenderer'];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());
        foreach ($needles as $needle) {
            if (str_contains($contents, $needle)) {
                $signals[] = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname()) . ' contains ' . $needle;
                break;
            }
        }

        if (count($signals) >= 50) {
            break;
        }
    }

    return array_values(array_unique($signals));
}

function detectSafePattern(string $source, array $renderSignals): ?string
{
    if ($source === '' || $renderSignals === []) {
        return null;
    }

    // Intentionally conservative. Future source-specific patch phases should add named patterns here.
    if (str_contains($source, 'RoleAdminController') && (str_contains($source, '<form') || str_contains($source, '<table') || str_contains($source, '<<<'))) {
        return null;
    }

    return null;
}
