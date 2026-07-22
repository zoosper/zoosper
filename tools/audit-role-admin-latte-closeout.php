<?php

declare(strict_types=1);

/**
 * Read-only closeout gate for the RoleAdminController Latte migration.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
$enforceClosed = false;

foreach ($argv as $argument) {
    if ($argument === '--enforce-closed') {
        $enforceClosed = true;
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
$criteriaDoc = 'docs/development/role-admin-latte-closeout.md';
$handoffDoc = 'docs/development/role-admin-latte-closeout-handoff.md';

$errors = [];
$blockers = [];

if ($controllerPath === null) {
    $errors[] = 'RoleAdminController.php was not found under app/.';
}

$source = $controllerPath !== null ? (string) file_get_contents($controllerPath) : '';

$checks = [
    'controller_found' => $controllerPath !== null,
    'index_template_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $indexTemplate)),
    'form_template_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $formTemplate)),
    'criteria_doc_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $criteriaDoc)),
    'handoff_doc_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $handoffDoc)),
    'controller_mentions_role' => stripos($source, 'role') !== false,
    'controller_mentions_csrf_or_template_mentions_csrf' => stripos($source, 'csrf') !== false || templateMentions($root, [$indexTemplate, $formTemplate], 'csrf'),
    'controller_has_large_inline_markup' => hasLargeInlineMarkup($source),
];

foreach (['controller_found', 'index_template_exists', 'form_template_exists', 'criteria_doc_exists', 'handoff_doc_exists'] as $required) {
    if (! $checks[$required]) {
        $errors[] = $required . ' failed';
    }
}

if ($checks['controller_has_large_inline_markup']) {
    $blockers[] = 'RoleAdminController still appears to contain large inline form/table/heredoc markup.';
}

if (! $checks['controller_mentions_role']) {
    $blockers[] = 'Role source signal was not found in RoleAdminController.';
}

if (! $checks['controller_mentions_csrf_or_template_mentions_csrf']) {
    $blockers[] = 'CSRF source signal was not found in controller or role templates.';
}

$status = ($errors === [] && $blockers === []) ? 'closed' : 'open';

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-latte-closeout.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-latte-closeout.log';

$report = [];
$report[] = '# RoleAdminController Latte Closeout Gate';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Controller path: ' . ($controllerPath ?? 'not found');
$report[] = 'Closeout status: ' . $status;
$report[] = 'Errors: ' . count($errors);
$report[] = 'Blockers: ' . count($blockers);
$report[] = '';
$report[] = '## Checks';
$report[] = '';
foreach ($checks as $name => $passed) {
    $report[] = '- ' . $name . ': ' . ($passed ? 'yes' : 'no');
}

if ($blockers !== []) {
    $report[] = '';
    $report[] = '## Blockers';
    foreach ($blockers as $blocker) {
        $report[] = '- ' . $blocker;
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
$report[] = '## Interpretation';
$report[] = '';
$report[] = $status === 'closed'
    ? 'Phase 1.38 closeout criteria are satisfied by this gate. Run the full Pest suite before claiming closure.'
    : 'Phase 1.38 is not closed yet. Use the blockers above to drive the next source-specific implementation phase.';

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Role admin latte closeout report written to: ' . $txtPath;
$log[] = 'CLOSEOUT_STATUS ' . $status;
$log[] = 'CONTROLLER_FOUND ' . ($controllerPath !== null ? 'yes' : 'no');
$log[] = 'INLINE_MARKUP ' . ($checks['controller_has_large_inline_markup'] ? 'yes' : 'no');
$log[] = 'CLOSEOUT_ERRORS ' . count($errors);
$log[] = 'CLOSEOUT_BLOCKERS ' . count($blockers);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

if ($errors !== []) {
    exit(1);
}

if ($enforceClosed && $status !== 'closed') {
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

function hasLargeInlineMarkup(string $source): bool
{
    if ($source === '') {
        return false;
    }

    return str_contains($source, '<<<')
        || substr_count($source, '<form') >= 1
        || substr_count($source, '<table') >= 1
        || substr_count($source, '<input') >= 3;
}

/** @param list<string> $templatePaths */
function templateMentions(string $root, array $templatePaths, string $needle): bool
{
    foreach ($templatePaths as $templatePath) {
        $absolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $templatePath);
        if (is_file($absolute) && stripos((string) file_get_contents($absolute), $needle) !== false) {
            return true;
        }
    }

    return false;
}
