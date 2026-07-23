<?php

declare(strict_types=1);

/**
 * Discover the exact RoleAdminController source pattern for the Latte cutover.
 * Expected method excerpt files include method-index.txt, method-createForm.txt, and method-editForm.txt.
 *
 * This command is read-only. It writes reports and source excerpts under var/reports only.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

$sourceDir = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-pattern-source';
if (! is_dir($sourceDir) && ! mkdir($sourceDir, 0775, true) && ! is_dir($sourceDir)) {
    fwrite(STDERR, 'Unable to create source output directory: ' . $sourceDir . PHP_EOL);
    exit(1);
}

$controllerRelative = 'app/zoosper-admin/src/Controller/RoleAdminController.php';
$controllerPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $controllerRelative);
$adminLayoutPath = findFile($root . DIRECTORY_SEPARATOR . 'app', 'AdminLayout.php');
$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-pattern.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-pattern.log';
$errors = [];

if (! is_file($controllerPath)) {
    $errors[] = 'Controller not found: ' . $controllerRelative;
}

if ($adminLayoutPath === null) {
    $errors[] = 'AdminLayout.php was not found under app/.';
}

$controllerSource = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$layoutSource = $adminLayoutPath !== null ? (string) file_get_contents($adminLayoutPath) : '';

if ($controllerSource !== '') {
    file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'RoleAdminController.php', $controllerSource);
}

if ($layoutSource !== '') {
    file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'AdminLayout.php', $layoutSource);
}

$targetMethods = ['index', 'createForm', 'editForm', 'create', 'update'];
$methodSignals = [];
foreach ($targetMethods as $method) {
    $body = extractMethodSource($controllerSource, $method);
    $path = $sourceDir . DIRECTORY_SEPARATOR . 'method-' . $method . '.txt';
    file_put_contents($path, $body === null ? 'METHOD_NOT_FOUND' . PHP_EOL : $body);
    $methodSignals[$method] = [
        'found' => $body !== null,
        'contains_form' => $body !== null && str_contains($body, '<form'),
        'contains_table' => $body !== null && str_contains($body, '<table'),
        'contains_input' => $body !== null && str_contains($body, '<input'),
        'contains_heredoc' => $body !== null && str_contains($body, '<<<'),
        'mentions_layout_render' => $body !== null && str_contains($body, 'layout->render'),
        'mentions_csrf' => $body !== null && stripos($body, 'csrf') !== false,
    ];
}

$constructorParams = constructorParams($controllerSource);
file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'constructor-params.txt', implode(PHP_EOL, $constructorParams) . PHP_EOL);

$layoutMethods = publicMethods($layoutSource);
file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'admin-layout-public-methods.txt', implode(PHP_EOL, $layoutMethods) . PHP_EOL);

$report = [];
$report[] = '# RoleAdminController Cutover Pattern Discovery';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Controller: ' . $controllerRelative;
$report[] = 'AdminLayout: ' . ($adminLayoutPath ?? 'not found');
$report[] = 'Source output directory: ' . $sourceDir;
$report[] = 'Errors: ' . count($errors);
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
$report[] = '## Method signals';
foreach ($methodSignals as $method => $signals) {
    $report[] = '';
    $report[] = '### ' . $method;
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
$log[] = 'Role admin cutover pattern report written to: ' . $reportPath;
$log[] = 'SOURCE_DIR ' . $sourceDir;
$log[] = 'CONTROLLER_FOUND ' . (is_file($controllerPath) ? 'yes' : 'no');
$log[] = 'ADMIN_LAYOUT_FOUND ' . ($adminLayoutPath !== null ? 'yes' : 'no');
$log[] = 'PATTERN_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

exit($errors === [] ? 0 : 1);

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
function constructorParams(string $source): array
{
    if (! preg_match('/function\s+__construct\s*\((.*?)\)/s', $source, $match)) {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $match[1]))));
}

/** @return list<string> */
function publicMethods(string $source): array
{
    if (! preg_match_all('/public\s+function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $source, $matches)) {
        return [];
    }

    return array_values(array_unique($matches[1]));
}

function extractMethodSource(string $source, string $method): ?string
{
    $pattern = '/public\s+function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)\s*(?::\s*[^\{]+)?\{/m';
    if (! preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE)) {
        return null;
    }

    $start = $match[0][1];
    $brace = strpos($source, '{', $start);
    if ($brace === false) {
        return null;
    }

    $depth = 0;
    $length = strlen($source);
    for ($i = $brace; $i < $length; $i++) {
        $char = $source[$i];
        if ($char === '{') {
            $depth++;
        } elseif ($char === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($source, $start, $i - $start + 1);
            }
        }
    }

    return null;
}
