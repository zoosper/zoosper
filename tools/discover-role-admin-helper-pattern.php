<?php

declare(strict_types=1);

/**
 * Discover RoleAdminController helper method source for the Latte cutover.
 *
 * Expected helper excerpt files include method-form.txt, method-html.txt, method-e.txt,
 * method-currentAdminUser.txt, and method-roleFromRequest.txt.
 * This command is read-only. It writes reports and source excerpts under var/reports only.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

$sourceDir = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern-source';
if (! is_dir($sourceDir) && ! mkdir($sourceDir, 0775, true) && ! is_dir($sourceDir)) {
    fwrite(STDERR, 'Unable to create source output directory: ' . $sourceDir . PHP_EOL);
    exit(1);
}

$controllerRelative = 'app/zoosper-admin/src/Controller/RoleAdminController.php';
$controllerPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $controllerRelative);
$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-helper-pattern.log';
$errors = [];

if (! is_file($controllerPath)) {
    $errors[] = 'Controller not found: ' . $controllerRelative;
}

$source = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
if ($source !== '') {
    file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'RoleAdminController.php', $source);
}

$methods = [
    'index',
    'createForm',
    'editForm',
    'create',
    'update',
    'html',
    'form',
    'e',
    'currentAdminUser',
    'roleFromRequest',
];

$signals = [];
foreach ($methods as $method) {
    $body = extractMethodSource($source, $method);
    $path = $sourceDir . DIRECTORY_SEPARATOR . 'method-' . $method . '.txt';
    file_put_contents($path, $body === null ? 'METHOD_NOT_FOUND' . PHP_EOL : $body);
    $signals[$method] = [
        'found' => $body !== null,
        'visibility_public' => $body !== null && str_starts_with(ltrim($body), 'public function'),
        'visibility_private' => $body !== null && str_starts_with(ltrim($body), 'private function'),
        'contains_form_markup' => $body !== null && str_contains($body, '<form'),
        'contains_table_markup' => $body !== null && str_contains($body, '<table'),
        'contains_input_markup' => $body !== null && str_contains($body, '<input'),
        'contains_heredoc' => $body !== null && str_contains($body, '<<<'),
        'mentions_layout_render' => $body !== null && str_contains($body, 'layout->render'),
        'mentions_csrf' => $body !== null && stripos($body, 'csrf') !== false,
    ];
}

$report = [];
$report[] = '# RoleAdminController Helper Pattern Discovery';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Controller: ' . $controllerRelative;
$report[] = 'Source output directory: ' . $sourceDir;
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Helper conclusions';
$report[] = '- index owns list/table rendering when it contains table markup.';
$report[] = '- createForm and editForm depend on the form helper.';
$report[] = '- form/html helper source is required for the final safe cutover patch.';
$report[] = '';
$report[] = '## Method signals';
foreach ($signals as $method => $methodSignals) {
    $report[] = '';
    $report[] = '### ' . $method;
    foreach ($methodSignals as $name => $value) {
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
$log[] = 'Role admin helper pattern report written to: ' . $reportPath;
$log[] = 'SOURCE_DIR ' . $sourceDir;
$log[] = 'CONTROLLER_FOUND ' . (is_file($controllerPath) ? 'yes' : 'no');
$log[] = 'HELPER_PATTERN_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

exit($errors === [] ? 0 : 1);

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
