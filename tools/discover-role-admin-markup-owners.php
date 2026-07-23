<?php

declare(strict_types=1);

/**
 * Discover all RoleAdminController methods that still own inline markup.
 *
 * Expected output includes role-admin-markup-owners.txt and method-<name>.txt excerpt files.
 * This command is read-only and writes reports under var/reports only.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

$sourceDir = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-markup-owners-source';
if (! is_dir($sourceDir) && ! mkdir($sourceDir, 0775, true) && ! is_dir($sourceDir)) {
    fwrite(STDERR, 'Unable to create source output directory: ' . $sourceDir . PHP_EOL);
    exit(1);
}

$controllerRelative = 'app/zoosper-admin/src/Controller/RoleAdminController.php';
$controllerPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $controllerRelative);
$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-markup-owners.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-markup-owners.log';
$errors = [];

if (! is_file($controllerPath)) {
    $errors[] = 'Controller not found: ' . $controllerRelative;
}

$source = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$methods = methodNames($source);
$ownership = [];

foreach ($methods as $method) {
    $body = extractMethodSource($source, $method);
    $path = $sourceDir . DIRECTORY_SEPARATOR . 'method-' . $method . '.txt';
    file_put_contents($path, $body ?? 'METHOD_NOT_FOUND' . PHP_EOL);
    $ownership[$method] = markupSignals($body ?? '');
}

$blockers = [];
foreach ($ownership as $method => $signals) {
    if ($signals['owns_markup']) {
        $blockers[] = $method;
    }
}

$report = [];
$report[] = '# RoleAdminController Markup Ownership Map';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Controller: ' . $controllerRelative;
$report[] = 'Source output directory: ' . $sourceDir;
$report[] = 'Errors: ' . count($errors);
$report[] = 'Methods scanned: ' . count($methods);
$report[] = 'Markup-owning methods: ' . count($blockers);
$report[] = '';
$report[] = '## Markup-owning methods';
if ($blockers === []) {
    $report[] = '- none';
} else {
    foreach ($blockers as $method) {
        $report[] = '- ' . $method;
    }
}
$report[] = '';
$report[] = '## Method signals';
foreach ($ownership as $method => $signals) {
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
$log[] = 'Role admin markup ownership report written to: ' . $reportPath;
$log[] = 'SOURCE_DIR ' . $sourceDir;
$log[] = 'CONTROLLER_FOUND ' . (is_file($controllerPath) ? 'yes' : 'no');
$log[] = 'METHODS_SCANNED ' . count($methods);
$log[] = 'MARKUP_METHODS ' . count($blockers);
$log[] = 'MARKUP_OWNER_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);

/** @return list<string> */
function methodNames(string $source): array
{
    if (! preg_match_all('/(?:public|private|protected)\s+function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $source, $matches)) {
        return [];
    }
    return array_values(array_unique($matches[1]));
}

/** @return array<string,bool> */
function markupSignals(string $body): array
{
    $signals = [
        'contains_form' => str_contains($body, '<form'),
        'contains_table' => str_contains($body, '<table'),
        'contains_input' => str_contains($body, '<input'),
        'contains_label' => str_contains($body, '<label'),
        'contains_list' => str_contains($body, '<ul') || str_contains($body, '<li'),
        'contains_anchor' => str_contains($body, '<a '),
        'contains_heredoc' => str_contains($body, '<<<'),
    ];
    $signals['owns_markup'] = in_array(true, $signals, true);
    return $signals;
}

function extractMethodSource(string $source, string $method): ?string
{
    $pattern = '/(?:public|private|protected)\s+function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)\s*(?::\s*[^\{]+)?\{/m';
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
