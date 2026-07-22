<?php

declare(strict_types=1);

/**
 * Read-only audit for the RoleAdminController Latte migration readiness phase.
 *
 * This command does not rewrite source files.
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

$controllerPath = findFile($root . DIRECTORY_SEPARATOR . 'app', 'RoleAdminController.php');
$errors = [];

if ($controllerPath === null) {
    $errors[] = 'RoleAdminController.php was not found under app/';
}

$source = $controllerPath !== null ? (string) file_get_contents($controllerPath) : '';

$signals = [
    'controller_found' => $controllerPath !== null,
    'mentions_role_admin_controller' => str_contains($source, 'RoleAdminController'),
    'mentions_csrf' => stripos($source, 'csrf') !== false,
    'mentions_permission_or_role' => stripos($source, 'permission') !== false || stripos($source, 'role') !== false,
    'contains_possible_inline_html' => str_contains($source, '<form') || str_contains($source, '<table') || str_contains($source, '<<<'),
    'migration_doc_exists' => is_file($root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'development' . DIRECTORY_SEPARATOR . 'role-admin-latte-migration.md'),
];

foreach (['controller_found', 'mentions_role_admin_controller', 'migration_doc_exists'] as $required) {
    if (! $signals[$required]) {
        $errors[] = $required . ' failed';
    }
}

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-latte-readiness.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-latte-readiness.log';

$report = [];
$report[] = '# RoleAdminController Latte Migration Readiness';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Controller path: ' . ($controllerPath ?? 'not found');
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Signals';
$report[] = '';

foreach ($signals as $name => $passed) {
    $report[] = '- ' . $name . ': ' . ($passed ? 'yes' : 'no');
}

$report[] = '';
$report[] = '## Notes';
$report[] = '';
$report[] = '- This audit is read-only.';
$report[] = '- Possible inline HTML is a migration signal, not a failure in this readiness phase.';
$report[] = '- A later implementation phase should add templates and remove controller-owned markup.';

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Role admin latte readiness written to: ' . $txtPath;
$log[] = 'CONTROLLER_FOUND ' . ($controllerPath !== null ? 'yes' : 'no');
$log[] = 'POSSIBLE_INLINE_HTML ' . ($signals['contains_possible_inline_html'] ? 'yes' : 'no');
$log[] = 'READINESS_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

if ($errors !== []) {
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
